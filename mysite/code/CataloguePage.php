<?php
class CataloguePage extends Page
{}

class CataloguePage_Controller extends Page_Controller 
{
    
    private static $allowed_actions = array('Form', 'edit', '__getKeywords', 'savePosterPreview');
    
    public function Form()
    {
        
         $genres = $this->__getGenres();
         $clean = parent::__convertAndCleanList($genres, $pipe='|');
         ($genres != null) ?  $genresJson = json_encode($clean) : json_encode(array("Comedy", "Drama", "Horror", "Science Fiction", "Comic/Super Heroes", "Action", "Thriller", "Crime", "Documentary" , "Family", "Animated", "Romance", "Adventure", "War", "Sitcom"));
         
         Requirements::customScript('
                $("#Form_Form_Seasons").tagit({
                    singleFieldDelimiter: " | ",    
                    allowSpaces: true,
                    availableTags: ["Season 1", "Season 2", "Season 3", "Season 4", "Season 5", "Season 6", "Season 7", "Season 8", "Season 9" , "Season 10", "Season 11", "Season 12", "Season 13", "Season 14", "Season 15", "Season 16"]
                });
                
                $("#Form_Form_Genre").tagit({
                    singleFieldDelimiter: " | ",    
                    availableTags: '.$genresJson.'
                });
                                     
            ');
            
        //create source arrays to swap out on form load
        $filmarr = array(
                      'Bluray'      =>'BD/BRRip',
                      'DVD'         => 'DVD-R',
                      'screener'    => 'SCR/SCREENER/DVDSCR/DVDSCREENER/BDSCR',
                      'cam'         => 'CAMRip/CAM/TS/TELESYNC',
                      'vod'         => 'VODRip/VODR',
                      'web'         => 'WEB-Rip/WEBRIP/WEB Rip/WEB-DL');
        $tvarr = array( 
                      'Bluray'  => 'BD/BRRip',
                      'DVD'     => 'DVD-R',
                      'HDTV'    => 'HD TV',
                      'SDTV'    => 'SD TV',
                      'web'     => 'WEB-Rip/WEBRIP/WEB Rip/WEB-DL');
                
        //include js
        $keywords = $this->__getKeywords();
        if($keywords != null)
        {
            $clean = parent::__convertAndCleanList($keywords, $pipe=',');
            $json = json_encode($clean); // turn into json array for jquery library
            
            Requirements::customScript('
              $("#Form_Form_keywords").tagit({
                    singleFieldDelimiter: " , ",
                    allowSpaces: true,
                    availableTags: '. $json .'
                });
            ');
        } else {
            Requirements::customScript('
              $("#Form_Form_keywords").tagit({
                    singleFieldDelimiter: " , ",
                    allowSpaces: true,
                });
            ');
        }
        
        $action = Controller::curr()->getRequest()->param('Action');
        $id = (int)Controller::curr()->getRequest()->param('ID');
        $automap = ($id)? $automap = Catalogue::get()->byID($id) : false;
      
        $submitCaption = ($automap) ? 'Edit' : 'Add';
        $sourceArr = $filmarr;
        if(isset($automap->Video_type)) ($automap->Video_type == 'film') ? $sourceArr = $filmarr : $sourceArr = $tvarr;
        // Create fields
        $fields = FieldList::create(
            TextField::create('Video_title', 'Video Title'),
            DropDownField::create('Video_type', 'Type of Video', array('series'=>'Series (TV)', 'film'=>'Film'))->setEmptyString('Select type of media'),
            TextField::create('Genre', 'Genre')->setDescription('Select a genre by typing a keyword e.g. Comedy'),
            TextField::create('keywords', 'Keywords')->setDescription('Add a keyword/tag to the title e.g. Marvel'),
            TextField::create('Seasons', 'Seasons')->setDescription('Select a Season or type Seasons owned e.g. Season 1'),
            DropDownField::create('Status', 'Current Status of download', array(
                'Downloaded' => 'Downloaded - file complete',
                'Downloading' => 'Dowloading - in progress',
                'Wanted' => 'Wanted - need a copy of',
                'No Torrents' => 'No Torrents - cannot find video',
            ))->setEmptyString('Select status'),
            DropDownField::create('Source', 'Source of download', $sourceArr)->setEmptyString('Select source'),
            DropDownField::create('Quality', 'Resolution of download (quality)', array(
                '4k' => '4k',
                '1440p' => '1440p',
                '1080p' => '1080p',
                '720p' => '720p',
                '420p' => '420p',
                '320p' => '320p'
            ))->setEmptyString('Select quality'),
            HiddenField::create('Owner', '', Member::currentUserID()),
            HiddenField::create('Comments'),
            HiddenField::create('imdbID'),
            HiddenField::create('Year'),
            TextareaField::create('CommentsEnter', 'Enter new comments on new line'),
            HiddenField::create('Poster'),
            HiddenField::create('ID', 'ID')->setValue($id) 
            
        );

        $actions = FieldList::create( 
             FormAction::create('submit', $submitCaption) 
        );
        
        $validator = RequiredFields::create('Video_title');
        $form = Form::create($this, 'Form', $fields, $actions, $validator);
        $form->type = $submitCaption; //are we in edit or add mode, pass it to view
        
        if ($automap) $form->loadDataFrom($automap);

        return $form;
    }

    public function submit($data, $form)
    {
        $automap = Catalogue::create();
        $form->saveInto($automap);
        $automap->ID = $data['ID'];
        $automap->Last_updated = SS_Datetime::now()->format('Y-m-d H:i:s');
        ($automap->validate == false) ? $id = $automap->write() : $id = null;
        
        if ($id !== null)
        { 
            Session::setFormMessage($form->FormName(), $data['Video_title'], 'has been saved to the catalogue. <br><a href="video-profile/'.$id.'">Preview changes</a>', 'success');
            $this->redirect($this->Link() . "edit/$id");
        } elseif($id === null)
        {
            Session::setFormMessage($form->FormName(), $data['Video_title'], " is already in the catalogue.", 'bad');
            $this->redirect($this->Link()); 
        } else {
            Session::setFormMessage($form->FormName(), 'Something went wrong.');
        }    
           
    }

    /**
     * 
     * @param object
     * @desc does a request to get the poster and save it to local server before insert gives preview to user.
     * 
     */
    public function savePosterPreview($poster)
    {
        $url = $poster['poster'];
        $title = $poster['title'];

        //$url='http://ia.media-imdb.com/images/M/MV5BMjI5OTYzNjI0Ml5BMl5BanBnXkFtZTcwMzM1NDA1OQ@@._V1_SX300.jpg';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec ($ch);
        curl_close ($ch);
        
        $result = base64_encode($result);
        $src = 'data: content-type: image/jpeg;base64,'.$result;
        file_put_contents(POSTERSDIR."{$title}.jpg", file_get_contents($src)); //save it to local server
        $result = '<img src="'.$src.'">';
        
        return $result;
    }


    /**
     * gets distinct all keywords from records 
     * 
     * @return object
     */    
    public function __getKeywords()
    {
        $result = Catalogue::get()->sort('keywords')->where('keywords is not null')->column($colName = "keywords"); 
        
        return $result;
    }
    
    /**
     * gets distinct all Genres from records 
     * 
     */    
    public function __getGenres()
    {
        $result = Catalogue::get()->sort('Genre')->where('Genre is not null')->column($colName = "Genre"); 
        
        return $result;
    }
    
} 

