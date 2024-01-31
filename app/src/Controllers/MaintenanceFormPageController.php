<?php

namespace App\Catalogue\PageTypes;

use App\Catalogue\Api\Constants\Constants;
use App\Catalogue\ApiServices\ApiService;
use App\Catalogue\Models\Catalogue;
use PageController;
use SilverStripe\Assets\Image;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ValidationException;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\View\Requirements;

class MaintenanceFormPageController extends PageController
{

    /**
     * @var array|string[]
     * @todo remove redundant allowed actions
     */
    private static array $allowed_actions = [
        'Form',
        'edit',
        'getKeywords',
        'fetchPosterImage',
    ];

    private static array $url_handlers = [
        'poster/$poster' => 'fetchPosterImage',
    ];

    //set up types of sources for movies
    private static array $moviesSourceArray = [
        'Bluray' => 'BD/BRRip',
        'DVD' => 'DVD',
        'screener' => 'SCR/SCREENER/DVDSCR/DVDSCREENER/BDSCR',
        'cam' => 'CAMRip/CAM/TS/TELESYNC',
        'vod' => 'VODRip/VODR',
        'WebM' => 'WEB-Rip/WEBRIP/WEB Rip/WEB-DL',
    ];

    // set up types of sources for television
    private static array $seriesSourceArray = [
        'Bluray' => 'BD/BRRip',
        'DVD' => 'DVD',
        'HDTV' => 'HD TV',
        'SDTV' => 'SD TV',
        'WebM' => 'WEB-Rip/WEBRIP/WEB Rip/WEB-DL',
    ];

    protected function init(): void
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

    public function Form(): Form
    {
        $genres = $this->getMetadataFilters($this->ClassName, 'Genre', 'javascript')
             ?? json_encode(self::$genresDefaultList);

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

        if ($keywords !== null) {
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
        $catalogueRecord = Catalogue::get()->byID($this->slug);
        $automap = $this->slug ? $catalogueRecord : null;

        $submitCaption = $automap ? 'Update' : 'Add';

        $sourceArr = match ($automap->Type ?? null) {
            'tv' => self::$seriesSourceArray,
            default => self::$moviesSourceArray
        };

        // Create fields
        $fields = FieldList::create(
            TextField::create('Title', 'Video Title'),
            DropDownField::create('Type', 'Type of Video', [
                'series' => 'Television Series',
                'movie' => 'Film/Movie',
            ])->setEmptyString('Select type of media'),
            TextField::create('Genre', 'Genre')
                ->setDescription('Tag a genre by typing e.g. Comedy'),
            TextField::create('Keywords', 'Keywords')
                ->setDescription('Tag the title with a keyword e.g. Marvel'),
            TextField::create('Collection', 'Is this part of a collection?')
                ->setDescription(
                    'This should match one of your keywords e.g. add a collection name "Skywalker Saga".'
                ),
            TextField::create('Seasons', 'Seasons')
                ->setDescription('Select seasons you have e.g. Season 2'),
            DropDownField::create(
                'Status',
                'Current Status of title',
                [
                    'Downloaded' => 'Downloaded - file complete',
                    'Online' => 'Online - streaming service only',
                    'Physical' => 'Physical - hard copy only',
                    'Downloading' => 'Downloading - in progress',
                    'Wanted' => 'Wanted - need a copy of',
                    'No Torrents' => 'No Torrents - cannot find video',
                ]
            )->setEmptyString('Select status'),
            DropDownField::create(
                'Source',
                'Source of download',
                $sourceArr
            )->setEmptyString('Select source'),
            // @todo refactor this into global array
            DropDownField::create(
                'Quality',
                'Resolution of download (quality)',
                [
                    '4k' => '4k - top quality',
                    '1440p' => '1440p - amazing quality',
                    '1080p' => '1080p - great quality',
                    '720p' => '720p - good quality',
                    '480p' => '480p - average quality',
                ]
            )->setEmptyString('Select quality'),
            HiddenField::create('OwnerID', '', $this->member),
            HiddenField::create('Comments'),
            HiddenField::create('ImdbID'),
            HiddenField::create('Year'),
            HiddenField::create('PosterID'),
            HiddenField::create('PosterURL'),
            HiddenField::create('ID', 'ID')->setValue($this->slug)
        );

        $actions = FieldList::create(
            FormAction::create('submit', $submitCaption)
                 ->setUseButtonTag(true)
                 ->addExtraClass('btn btn-primary')
        );

        $validator = RequiredFields::create('Title');
        $form = Form::create($this, 'Form', $fields, $actions, $validator);

        // @todo update to use Folder class instead of hard link. (Not sure what this comments means)
        $posterEndpoint = Controller::join_links(PageController::Link(), 'fetchPosterImage');
        $form->setAttribute('data-posterlink', $posterEndpoint);
        $form->type = $submitCaption; //are we in edit or add mode, pass it to view

        if ($automap) {
            $form->loadDataFrom($automap);
        }

        return $form;
    }

    /**
     * @param $data
     * @param Form $form
     * @return void
     * @throws ValidationException
     * @todo this needs updating, we should be calling update not create.
     */
    public function submit($data, Form $form): void
    {
        $automap = Catalogue::create();
        $form->saveInto($automap);
        $automap->ID = $data['ID'];

        $id = $automap->validate()->isValid()
            ? $automap->write()
            : null;

        if ($id === null) {
            $form->sessionMessage($data['Title']. ' is already in the catalogue.', ValidationResult::TYPE_WARNING);
            $this->redirect($this->Link());
        }

        if ($id !== null) {
            $message = '<strong>' . $data['Title'] . '</strong> ' .
            'has been saved to the catalogue. <br><a href="'.$this->getProfileURL().'title/'.$id.'">Preview changes</a>';

            $form->sessionMessage(
                $message,
                'good',
                ValidationResult::CAST_HTML
            );
            $this->redirectBack();
        } else {
            $form->sessionError('Something went wrong.');
        }
    }

    /**
     * Builds source arrays for maintenance forms
     */
    public function getSourceArrayTypes(string $type): ?string
    {
        if (is_array(self::$$type)) {
            $jsArray = '';

            foreach (self::$$type as $key => $value) {
                $jsArray .= '{val : \''.$key.'\', text: \''.$value.'\'},'."\r\t\t\t\t";
            }

            return $jsArray;
        }

        return null;
    }

    /**
     * Saves poster image from IMDB to assets folder determined by config settings.
     * example:
     *  `$url='http://ia.media-imdb.com/images/M/MV5BMjI5OTYzNjI0Ml5BMl5BanBnXkFtZTcwMzM1NDA1OQ@@._V1_SX300.jpg';`.
     *
     * Makes a request to get the poster and save it to local flysystem
     * before insert gives preview to user.
     *
     */
    public function fetchPosterImage(HTTPRequest $request): string|Image|HTTPResponse
    {
        $posterData = $request->getVars();

        // No params were found so presume it is invalid.
        if (!$posterData) {
            $this->httpError(400, Constants::DEFAULT_HTTP_BAD_REQUEST_MESSAGE);
        }

        $catalogueItem = Catalogue::get_by_id($posterData['ID']);

        // Grab our service build a request and then call OMDB Api.
        $service = new ApiService();
        $posterImageSrc = $service->getPosterImage($posterData['Poster']);

        $catalogueItem->hydratePosterFromResponse((object)$posterData, $posterImageSrc);

        // Check if this an ajax request and return back a string.
        if ($request->isAjax()) {
            return
                '<img data-posterid="' . $catalogueItem->Poster()->ID . '" ' .
                'src="' . $catalogueItem->Poster()->ScaleWidth(250)->getAbsoluteURL(). '" ' .
                'alt="' . $catalogueItem->Poster()->Title . '">';
        }

        return $catalogueItem->Poster;
    }

}
