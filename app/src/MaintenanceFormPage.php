<?php
class MaintenanceFormPage extends Page
{

}

class MaintenanceFormPage_Controller extends Page_Controller
{
    private static $allowed_actions = [
        'Form',
        'edit',
        'getKeywords',
        'savePosterPreview'
    ];

    private static $url_handlers = [
        'Poster/$poster' => 'savePosterPreview'
    ];

    //set up types of sources for movies
    private static $moviesSourceArray = [
        'Bluray'      => 'BD/BRRip',
        'DVD'         => 'DVD',
        'screener'    => 'SCR/SCREENER/DVDSCR/DVDSCREENER/BDSCR',
        'cam'         => 'CAMRip/CAM/TS/TELESYNC',
        'vod'         => 'VODRip/VODR',
        'WebM'        => 'WEB-Rip/WEBRIP/WEB Rip/WEB-DL',
    ];

    // set up types of sources for television
    private static $seriesSourceArray = [
        'Bluray'  => 'BD/BRRip',
        'DVD'     => 'DVD',
        'HDTV'    => 'HD TV',
        'SDTV'    => 'SD TV',
        'WebM'    => 'WEB-Rip/WEBRIP/WEB Rip/WEB-DL',
    ];

    public function init()
    {
         parent::init();
         Requirements::themedJavascript('tag-it.min');
         Requirements::customScript('
           let filmarr = [
                '. $this->getSourceArrayTypes('moviesSourceArray')
           .'];

           let tvarr = [
                '. $this->getSourceArrayTypes('seriesSourceArray').
           '];
          
         ');

         Requirements::themedJavascript('imdb_ajax');
    }

    public function Form()
    {
         $genres = $this->getMetadataFilters($this->ClassName, 'Genre', 'javascript') ?? json_encode(static::$genresDefaultList);
         $keywords = $this->getMetadataFilters($this->ClassName, 'Keywords', 'javascript');

         Requirements::customScript('
                $("#Form_Form_Seasons").tagit({
                    singleFieldDelimiter: ",",    
                    allowSpaces: true,
                    availableTags: ["Season 1", "Season 2", "Season 3", "Season 4", "Season 5", "Season 6", "Season 7", 
                    "Season 8", "Season 9" , "Season 10", "Season 11", "Season 12", "Season 13", "Season 14", 
                    "Season 15", "Season 16", "Season 17", "Season 18", "Season 19", "Season 20", "Season 21"]
                });
                
                $("#Form_Form_Genre").tagit({
                    singleFieldDelimiter: ",",    
                    availableTags: ['. $genres .']
                });
                                     
            ');

        if($keywords != null)
        {

            Requirements::customScript('
              $("#Form_Form_Keywords").tagit({
                    singleFieldDelimiter: ",",
                    allowSpaces: true,
                    availableTags: ['. $keywords .']
                });
            ');

        } else {
            Requirements::customScript('
              $("#Form_Form_Keywords").tagit({
                    singleFieldDelimiter: ",",
                    allowSpaces: true,
                });
            ');
        }

        // override slug because we need to check if we're logged in and if we have an ID slug
        $this->slug = (int)Controller::curr()->getRequest()->param('ID');
        $automap = ($this->slug) ? $automap = Catalogue::get()->byID($this->slug) : false;

        $submitCaption = ($automap) ? 'Update' : 'Add';

        if(isset($automap->Type)) {
            if($automap->Type == 'movie') {
                $sourceArr = self::$moviesSourceArray;
            } else {
                $sourceArr = self::$seriesSourceArray;
            }
        }

        // Create fields
        $fields = FieldList::create(
            TextField::create('Title', 'Video Title'),
            DropDownField::create('Type', 'Type of Video',
                [
                    'series' => 'Series (TV/Web)',
                    'movie'  => 'Movie'
                ]
            )->setEmptyString('Select type of media'),
            TextField::create('Genre', 'Genre')->setDescription('Tag a genre by typing e.g. Comedy'),
            TextField::create('Keywords', 'Keywords')->setDescription('Tag the title with a keyword e.g. Marvel'),
            TextField::create('Trilogy', 'Is this a Trilogy?')->setDescription('Add a trilogy name e.g. "X-Men" or "Wolverine". This should match one of your keywords'),
            TextField::create('Seasons', 'Seasons')->setDescription('Select seasons you have e.g. Season 2'),
            DropDownField::create('Status', 'Current Status of title',
                [
                    'Downloaded'  => 'Downloaded - file complete',
                    'Online'      => 'Online - streaming',
                    'Physical'    => 'Phyiscal copy - hard copy only',
                    'Downloading' => 'Dowloading - in progress',
                    'Wanted'      => 'Wanted - need a copy of',
                    'No Torrents' => 'No Torrents - cannot find video',
                ]
            )->setEmptyString('Select status'),
            DropDownField::create('Source', 'Source of download', $sourceArr ?? self::$moviesSourceArray)->setEmptyString('Select source'),
            // @todo refactor this into global array
            DropDownField::create('Quality', 'Resolution of download (quality)',
                [
                    '4k'    => '4k - top quality',
                    '1440p' => '1440p - amazing quality',
                    '1080p' => '1080p - great quality',
                    '720p'  => '720p - good quality',
                    '480p'  => '480p - average quality',
                ]
            )->setEmptyString('Select quality'),
            HiddenField::create('OwnerID', '', $this->member),
            HiddenField::create('Comments'),
            HiddenField::create('IMDBID'),
            HiddenField::create('Year'),
            TextareaField::create('CommentsEnter', 'Enter new comments on new line'),
            HiddenField::create('PosterID'),
            HiddenField::create('ID', 'ID')->setValue($this->slug)
        );

        $actions = FieldList::create(
             FormAction::create('submit', $submitCaption)
        );

        $validator = RequiredFields::create('Title');
        $form = Form::create($this, 'Form', $fields, $actions, $validator);
        $form->setAttribute('data-posterlink', $this->Link() .'Poster/');
        $form->type = $submitCaption; //are we in edit or add mode, pass it to view

        if ($automap) $form->loadDataFrom($automap);

        return $form;
    }

    public function submit($data, $form)
    {
        $automap = Catalogue::create();
        $form->saveInto($automap);
        $automap->ID = $data['ID'];
        $automap->LastEdited = SS_Datetime::now()->format('Y-m-d H:i:s');
        ($automap->validate == false) ? $id = $automap->write() : $id = null;

        if ($id !== null)
        {
            Session::setFormMessage($form->FormName(), $data['Title'].
                ' has been saved to the catalogue. <br><a href="'.$this->getProfileURL().'title/'.$id.'">Preview changes</a>',
                'success'
            );
            $this->redirect($this->Link() . 'edit/'.$this->slug);
        } elseif($id === null)
        {
            Session::setFormMessage($form->FormName(), $data['Title']. " is already in the catalogue.", 'bad');
            $this->redirect($this->Link());
        } else {
            Session::setFormMessage($form->FormName(), 'Something went wrong.', 'bad');
        }
    }

    /**
     * Saves poster image from IMDB to assets folder determined by config settings.
     * example: $url='http://ia.media-imdb.com/images/M/MV5BMjI5OTYzNjI0Ml5BMl5BanBnXkFtZTcwMzM1NDA1OQ@@._V1_SX300.jpg';
     * @param object
     * @desc does a request to get the poster and save it to local server before insert gives preview to user.
     *
     * @return bool|string
     * @throws ValidationException
     */
    public function savePosterPreview($poster)
    {
        $url = $poster['poster'];
        $title = $poster['title'] . " (". $poster['year'] . ")";
        $filename = $this->cleanFilename($poster['title'], $poster['imdbID'], 'image');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec ($ch);

        curl_close ($ch);

        $result = base64_encode($result);
        $src = 'data: content-type: image/jpeg;base64,'.$result;

        if($poster = $this->savePosterImage(null, $src, $filename, $title) )
        {
            $result = Image::get()->byID($poster->ID);

            return $image = '<img data-posterid="'.$result->ID.'" src="'.$result->scaleWidth(250)->Link().'" alt="'.$result->Title.'">';
        }
    }

    /**
     * Builds source arrays for maintenance forms
     * @param $type <string>
     * @return string
     */
    public function getSourceArrayTypes($type)
    {
        if(is_array(self::$$type)) {
            $jsArray = '';

            foreach (self::$$type as $key => $value) {
                $jsArray .= '{val : \''.$key.'\', text: \''.$value.'\'},'."\r\t\t\t\t";
            }
            return $jsArray;
        }

        return;
    }

}
