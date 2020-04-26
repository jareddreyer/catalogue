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

    public function init()
    {
         parent::init();
         Requirements::themedJavascript('tag-it.min');
         Requirements::themedJavascript('imdb_ajax');
    }

    public function Form()
    {
         $genres = $this->getGenres();

         ($genres !== null) ? $clean = parent::convertAndCleanList($genres, $pipe='|') : $clean = null;
         ($clean !== null) ?  $genresJson = json_encode($clean) : $genresJson = json_encode(["Comedy", "Drama", "Horror", "Science Fiction", "Comic/Super Heroes", "Action", "Thriller", "Crime", "Documentary" , "Family", "Animated", "Romance", "Adventure", "War", "Sitcom"]);

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

        //include js
        $keywords = $this->getKeywords();

        if($keywords != null)
        {
            $clean = parent::convertAndCleanList($keywords, $pipe=',');
            $json = json_encode($clean); // turn into json array for jquery library

            Requirements::customScript('
              $("#Form_Form_Keywords").tagit({
                    singleFieldDelimiter: " , ",
                    allowSpaces: true,
                    availableTags: '. $json .'
                });
            ');

        } else {
            Requirements::customScript('
              $("#Form_Form_Keywords").tagit({
                    singleFieldDelimiter: " , ",
                    allowSpaces: true,
                });
            ');
        }

        // override slug cause we need to check for null values specifically
        $this->slug = (int)Controller::curr()->getRequest()->param('ID');
        $automap = ($this->slug) ? $automap = Catalogue::get()->byID($this->slug) : false;

        $submitCaption = ($automap) ? 'Edit' : 'Add';

        if(isset($automap->Type)) {
            if($automap->Type == 'film') {
                $sourceArr = $this->getSourceTypes('film');
            } else {
                $sourceArr = $this->getSourceTypes('tv');
            }
        }

        // Create fields
        $fields = FieldList::create(
            TextField::create('Title', 'Video Title'),
            DropDownField::create('Type', 'Type of Video', ['series' =>'Series (TV/Web)' , 'film' =>'Film'])->setEmptyString('Select type of media'),
            TextField::create('Genre', 'Genre')->setDescription('Select a genre by typing a keyword e.g. Comedy'),
            TextField::create('Keywords', 'Keywords')->setDescription('Add a keyword/tag to the title e.g. Marvel'),
            TextField::create('Trilogy', 'Is this a Trilogy?')->setDescription('Add a trilogy name e.g. "X-Men" or "Wolverine"'),
            TextField::create('Seasons', 'Seasons')->setDescription('Select a Season or type Seasons owned e.g. Season 1'),
            DropDownField::create('Status', 'Current Status of title',
                [
                'Downloaded' => 'Downloaded - file complete',
                'Physical' => 'Phyiscal copy - hard copy only',
                'Downloading' => 'Dowloading - in progress',
                'Wanted' => 'Wanted - need a copy of',
                'No Torrents' => 'No Torrents - cannot find video',
                ]
            )->setEmptyString('Select status'),
            DropDownField::create('Source', 'Source of download', (isset($sourceArr)) ? $sourceArr : $this->getSourceTypes(null))->setEmptyString('Select source'),
            DropDownField::create('Quality', 'Resolution of download (quality)',
                [
                '4k' => '4k',
                '1440p' => '1440p',
                '1080p' => '1080p',
                '720p' => '720p',
                '420p' => '420p',
                '320p' => '320p'
                ]
            )->setEmptyString('Select quality'),
            HiddenField::create('OwnerID', '', Member::currentUserID()),
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
     * gets distinct all keywords from records
     *
     * @return array|bool
     */
    public function getKeywords()
    {
        $result = Catalogue::get();

        if($result->exists()) {
            return $result->sort('Keywords')->where('Keywords is not null')->column(  "Keywords");
        }

        return false;
    }

    /**
     * gets distinct all Genres from records
     *
     * @return object
     */
    public function getGenres()
    {
        $result = Catalogue::get();

        return ($result->exists()) ? $result->sort('Genre')->where('Genre is not null')->column( "Genre") : $result = null;
    }

    /**
     * Builds source arrays for maintenance forms
     * @param $type <string>
     * @return mixed
     */
    public function getSourceTypes($type)
    {
        switch ($type) {
            case 'film':
                $source = [
                    'Bluray'      => 'BD/BRRip',
                    'DVD'         => 'DVD-R',
                    'screener'    => 'SCR/SCREENER/DVDSCR/DVDSCREENER/BDSCR',
                    'cam'         => 'CAMRip/CAM/TS/TELESYNC',
                    'vod'         => 'VODRip/VODR',
                    'web'         => 'WEB-Rip/WEBRIP/WEB Rip/WEB-DL'
                ];
                break;

            case 'tv':
                $source = [
                    'Bluray'  => 'BD/BRRip',
                    'DVD'     => 'DVD-R',
                    'HDTV'    => 'HD TV',
                    'SDTV'    => 'SD TV',
                    'web'     => 'WEB-Rip/WEBRIP/WEB Rip/WEB-DL'
                ];
                break;
            default:
                $source = [
                    'Bluray'   => 'BD/BRRip',
                    'DVD'      => 'DVD-R',
                    'screener' => 'SCR/SCREENER/DVDSCR/DVDSCREENER/BDSCR',
                    'cam'      => 'CAMRip/CAM/TS/TELESYNC',
                    'vod'      => 'VODRip/VODR',
                    'web'      => 'WEB-Rip/WEBRIP/WEB Rip/WEB-DL',
                    'HDTV'     => 'HD TV',
                    'SDTV'     => 'SD TV'
                ];
                break;
        }

        return $source;
    }
}
