<?php

class Page extends SiteTree
{
    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();

        if( !MaintenanceFormPage::get()->first() ){
            $maintenanceFormPage = MaintenanceFormPage::create();
            $maintenanceFormPage->Title = 'Insert new media';
            $maintenanceFormPage->Content = '';
            $maintenanceFormPage->write();
            $maintenanceFormPage->publish('Stage', 'Live');
            $maintenanceFormPage->flushCache();
            DB::alteration_message('Catalog form page created', 'created');
        }

        if( !TelevisionPage::get()->first() ){
            $televisionPage = TelevisionPage::create();
            $televisionPage->Title = 'TV Shows';
            $televisionPage->Content = '';
            $televisionPage->write();
            $televisionPage->publish('Stage', 'Live');
            $televisionPage->flushCache();
            DB::alteration_message('TV shows page created', 'created');
        }

        if( !FilmsPage::get()->first() ){
            $filmsPage = FilmsPage::create();
            $filmsPage->Title = 'Movies';
            $filmsPage->Content = '';
            $filmsPage->write();
            $filmsPage->publish('Stage', 'Live');
            $filmsPage->flushCache();
            DB::alteration_message('Movies page created', 'created');
        }

        if( !ProfilePage::get()->first() ){
            $profilePage = ProfilePage::create();
            $profilePage->Title = 'Your Profile';
            $profilePage->Content = '';
            $profilePage->ShowInMenus = false;
            $profilePage->write();
            $profilePage->publish('Stage', 'Live');
            $profilePage->flushCache();
            DB::alteration_message('Catalog profile page created', 'created');
        }

        // delete about us and contact us pages from default install
        if( SiteTree::get_by_link('about-us')  || SiteTree::get_by_link('contact-us') ){
            $contactusPage = Page::get()->byID(3);
            $aboutusPage = Page::get()->byID(2);
            $contactusPage->delete();
            $aboutusPage->delete();
            DB::alteration_message("Deleting 'about us' & 'contact us' pages", 'deleted');
        }

        //set up assets so its nice and clean
        Folder::find_or_make(Config::inst()->get('Catalog', 'postersAssetsFolderName'));
        Folder::find_or_make(Config::inst()->get('Catalog', 'jsonAssetsFolderName'));
    }
}

class Page_Controller extends ContentController
{
    public $member, $slug, $apiKey, $postersAssetsFolderName, $jsonAssetsFolderName, $jsonPath, $postersPath;

	public function init()
    {
		parent::init();

        $themeDir = $this->ThemeDir();
        Requirements::set_write_js_to_body(true);
        Requirements::set_force_js_to_bottom(true);
        Requirements::set_combined_files_folder($themeDir.'/dist');
        // this needs to be higher up load order so blank images display before actual posts download

        Requirements::combine_files(
            'app.css',
            [
                $themeDir . '/css/reset.css',
                $themeDir . '/css/typography.css',
                $themeDir . '/css/form.css',
                $themeDir . '/css/layout.css',
                $themeDir . '/css/bootstrap.min.css',
                $themeDir . '/css/profile.css',
                $themeDir . '/css/jquery.tagit.css',
                $themeDir . '/css/jquery-ui-overrides.css',
                $themeDir . '/css/font-awesome.min.css',
                $themeDir . '/css/catalogue.css',
                $themeDir . '/css/homepage.css'
            ]
        );

		Requirements::javascript("https://code.jquery.com/jquery-1.12.4.min.js");
        Requirements::combine_files('app.js',
            [
               $themeDir . '/javascript/jquery-ui-1.10.4.custom.min.js',
               $themeDir . '/javascript/bootstrap.min.js',
               $themeDir . '/javascript/tag-it.min.js',
            ]);

        // @todo refactor when jplist-es6 has same functionality as jquery jplist
        if ($this->ClassName == 'FilmsPage' || $this->ClassName == 'TelevisionPage') {
            Requirements::themedCSS('jplist.core.min');
            Requirements::themedCSS('jplist.textbox-filter.min');
            Requirements::themedCSS('jplist.filter-toggle-bundle.min');
            Requirements::themedCSS('jplist.checkbox-dropdown.min');
            Requirements::themedJavascript('jplist.core.min');
            Requirements::themedJavascript('jplist.pagination-bundle.min');
            Requirements::themedJavascript('jplist.filter-dropdown-bundle.min');
            Requirements::themedJavascript('jplist.checkbox-dropdown.min');
            Requirements::themedJavascript('jplist.textbox-filter.min');
            Requirements::themedJavascript('jplist.history-bundle.min');
            Requirements::themedJavascript('jplist.counter-control.min');
            Requirements::themedJavascript('catalogue-scripts');
        }

        Requirements::customScript('
                $("a.scroll-arrow").mousedown( function(e) {
                             e.preventDefault();              
              			var container = $(this).parent().attr("id");                              
                               var direction = $(this).is("#scroll-right") ? "+=" : "-=";
                               var totalWidth = -$(".row__inner").width();
                               $(".row__inner .tile").each(function() {
                                   totalWidth += $(this).outerWidth(true);
                               });
                               
                               $("#"+ container + " .row__inner").animate({
                                   scrollLeft: direction + Math.min(totalWidth, 3000)
                                   
                               },{
                                   duration: 2500,
                                   easing: "swing", 
                                   queue: false }
                               );
                           }).mouseup(function(e) {
                             $(".row__inner").stop();
               });');

        // set up routing slugs
        $this->member = Member::currentUserID();
        $this->slug = (int)Controller::curr()->getRequest()->param('ID') ?? $this->member;

        // check if slug is set, if not then use currentMember()


        // get config variables
        $this->apiKey = Config::inst()->get('Catalog', 'apiKey');
        $this->postersAssetsFolderName = Config::inst()->get('Catalog', 'postersAssetsFolderName');
        $this->jsonAssetsFolderName = Config::inst()->get('Catalog', 'jsonAssetsFolderName');
        $this->jsonPath = ASSETS_PATH . $this->jsonAssetsFolderName;
        $this->postersPath = ASSETS_PATH . $this->postersAssetsFolderName;
	}

    /**
     * Grabs all members in the database and returns ID, Firstname & Surname
     * @todo needs refactoring, dont need an arrayList to do this method.
     *
     * @return object
     */
    public function getAllMembers()
    {
        $members = Member::get()->sort('FirstName')
            ->setQueriedColumns(
                ["ID" , "FirstName" , "Surname"]
            )
            ->exclude('ID', 1);

        $membersList = ArrayList::create();

        foreach ($members as $value)
        {
            $value->link = $this->URLSegment . '/user/';
            $membersList->push(
                            $value
                        );
        }

        return $membersList;
    }

    /**
     * returns Member object so can call Firstname and Lastname of users catalog.
     *
     * @return DataObject
     */
    public function getMember()
    {
        return Member::get_by_id(Member::class, $this->slug);
    }

    /**
     * Returns object for either newly added titles or
     * updated titles
     *
     * @param  string $type - 'added' or 'updated'
     *
     * @return object - titles
     */
    public function recentTitles ($type)
    {
        if ($type == 'added') {
            $recentlyAdded = Catalogue::get()
                ->where('Created BETWEEN (CURRENT_DATE() - INTERVAL 1 MONTH) AND CURRENT_DATE()')
                ->limit(15)
                ->sort('Created DESC');

            return $recentlyAdded;
        }

        if ($type == 'updated') {
            $recentlyUpdated = Catalogue::get()
                ->where('LastEdited is not null AND LastEdited > Created')
                ->limit(15)
                ->sort('LastEdited DESC');

            return $recentlyUpdated;
        }
    }

    /**
     * takes an array list and cleans it up ready to output as unique string
     * and sorts alphabetically.
     *
     * @param array $item
     * @param string $pipe
     * @return array
     */
    public function convertAndCleanList($item, $pipe)
    {
        if(is_array($item)) $implode = implode($pipe, $item);
        $csv = str_getcsv($implode ?? $item, $pipe);

        array_walk($csv, function(&$csv){ return $csv = trim($csv); } );
        $unique = array_keys(array_flip($csv));  //get only unique elements
        sort($unique, SORT_FLAG_CASE | SORT_STRING);

        return $unique;
    }

    /**
     * main call to return back cleaned up comments
     * @param $comments <string>
     * @return string
     *
     */
    public function displayComments ($comments)
    {
        $search = array('(', ')', '-'); //search for pipes around date
        $replace = array('</span><span class="timestamp">(', ')</span>', ' : <p>'); //surround them with html

        $comments = str_replace($search, $replace, $comments); //replace each occurence with new values
        $comments = explode(',', $comments); //break into array by comma

        $comments = array_map(function($comment) { return trim($comment, "'" ); }, $comments); //trim array elements and remove quotes

        $result = implode('</p><span class="name">',$comments); //break array into string

        return $result;
    }

    /**
    * gets the object from profile page type
    * which allows us to dynamically create the url to be linked from
    * as opposed to using static hardcoded paths.
    * @return  <string>
    *
    */
    public function getProfileURL()
    {
        return ProfilePage::get()->first()->Link();
    }

    /**
     * returns the maintenanceformpage class link
     * used by most pages so handy to reuse a method helper.
     * @return <string>
     */
    public function getMaintenanceFormPageLink()
    {
        return MaintenanceFormPage::get()->first()->Link();
    }

    /**
     * returns count of titles in catalogue by member
     *
     * @param $type string
     * @return string
     */
    public function getCountTitles($type)
    {
        if($count = Catalogue::get()->filter(['Type' => $type, 'OwnerID' => $this->slug])->count()) {
            return $count;
        }

        return false;
    }

    /**
     * checks if we have a poster saved to assets already
     * If it doesn't exist then it will save to DB and local file storage.
     *
     * @param $data
     * @param $filename
     * @return string
     * @throws ValidationException
     */
    public function checkPosterExists ($data, $filename)
    {
        // Get Catalogue PosterID ID
        $cataloguePosterID = Catalogue::get()->byID($this->slug);
        $poster = $data->{'Poster'};

        if(DataObject::get_one('Image', ['ID' => $cataloguePosterID->PosterID]) === false)
        {
            // save file and create dataobject image.
            $poster = $this->savePosterImage($cataloguePosterID->ID, $poster, $filename, $data->{'Title'}, $data->{'Year'} );

            return $poster;
        } else {
            return DataObject::get_one('Image', ['ID' => $cataloguePosterID->PosterID]);
        }
    }

    /**
     * allows saving of imdb posters to local storage
     *
     * @param $cataloguePosterID - ID of PosterID from Catalogue::class
     * @param $src - base64 of Poster image data (from IMDBApi)
     * @param $filename - what the image dataobject filename and local filename will be
     * @param $Title - Name and Title of media (from data sources)
     * @param $year - adds the year to the title if possible.
     * @return Image
     * @throws ValidationException
     * @see Catalogue::class
     */
    public function savePosterImage($cataloguePosterID = null,
                                    $src = null,
                                    $filename = null,
                                    $Title = null,
                                    $year = null)
    {
        // @todo this needs refactoring in SS4 to use assetsFileStore class
        if(($poster = DataObject::get_one('Image', ['Title' => $Title])) !== false)
        {
            $poster = DataObject::get_one('Image', ['Title' => $Title]);
        } else {

            // create asset folder path
            $assetsParentID = Folder::find_or_make($this->postersAssetsFolderName);

            // whole web path to posters
            $rawPosterPath = $this->postersPath . $filename;

            try {
                file_put_contents($rawPosterPath, file_get_contents($src));
            } catch (Exception $exception) {
                user_error('we had trouble saving posters to ' . $rawPosterPath );
            }

            $poster = Image::create();

            $poster->Title = $Title . ' (' . $year . ')';
            $poster->ParentID = $assetsParentID->ID;
            $poster->Filename = ASSETS_DIR . $this->postersAssetsFolderName . $filename;
            $poster->write();

            // update the catalogue record to now use a dataobject relationship ID if Catalogue record exists.
            if($cataloguePosterID !== null) {
                Catalogue::create()
                    ->update(
                        [
                            'ID'        => $cataloguePosterID,
                            'PosterID'  => $poster->ID,
                        ]
                    )->write();
            }
        }

        return $poster;
    }

    /**
     * returns poster image object for the template view.
     * @param $id
     * @return bool
     */
    public function getPosterImage($id)
    {
        if( ($poster = Image::get()->byID($id) ) !== null) {
            return $poster;
        }

        return 'blank.png';
    }

    /**
     * returns a safe title (removes characters unallowed in filenames)
     *
     * @param $title - name of media
     * @param $IMDBID - static IMDBID value
     * @param $fileType - image or text
     *
     * @return string
     */
    public function cleanFilename($title, $IMDBID, $fileType)
    {
        $sanitized = preg_replace('/[^a-zA-Z0-9-_\.]/','', $title);
        $filetype = ($fileType == 'image') ? '.jpg' : '.txt';

        return $sanitized .'-'. $IMDBID . $filetype;
    }

    /**
     * Creates filter of $type by 'Owner', and video 'Type', then returns as string ready
     * for json inclusion on the JpList panel.
     * Also sorts and removes duplicates
     *
     * @param $ClassName <string> - Used to set type ov media lookup
     * @param $filter <string> - Select Genres or Keywords.
     * @return string|void
     */
    public function getMetadataFilters($ClassName, $filter)
    {
        $result = Catalogue::get();
        $type = ($ClassName == 'FilmsPage') ? 'movie' : 'series';

        // films
        if($filter == 'Keywords') {
            $filtersResult = $result->filter(
                [
                    'Type'         => $type,
                    'OwnerID'      => $this->slug,
                    'Keywords:not' => ''
                ]
            )->column('Keywords');
        }

        if($filter == 'Genre') {
            $filtersResult = $result->filter(
                [
                    'Type'      => $type,
                    'OwnerID'   => $this->slug,
                    'Genre:not' => ''
                ]
            )->column('Genre');
        }

        if($result->exists() === true )
        {
            if(!empty($filtersResult) ) {
                // clean up and return distinct keywords from DB
                $filterArr = self::convertAndCleanList($filtersResult, ',');
                $filtersList = ArrayList::create();

                // build json string for both Keywords or Genres
                foreach ($filterArr as $filters) {

                    $filtersList->push(ArrayData::create(
                        [
                            'filters' =>
                                '<input id="'.$filters.'" data-path=".'. str_replace([' ',':'], '', $filters).'" type="checkbox">'."\n\r".
                                '<label for="'.$filters.'">'.$filters.' '.
                                '<span
                                     data-control-type="counter"
                                     data-control-action="counter"
                                     data-control-name="'.$filters.'-counter"
                                     data-format="({count})"
                                     data-path=".'.str_replace([' ',':'], '', $filters).'"
                                     data-mode="all"
                                     data-type="path"></span>'.
                                '</label>'
                        ]
                    ));
                }

                return $filtersList;
            }
        }

        return;
    }

    /**
     * Takes genres string element and splits them into array element for each genre
     *
     * @param $filter
     * @param $field
     * @return string
     */
    public function getFieldFiltersList($filter, $field)
    {
        $explode = explode(",", $field); //explode string to array by delimiter

        $listoption = '';

        foreach ($explode as $value)
        {
            $listoption .= '<span class="hidden '.str_replace([' ', ':'], '', $value).'">'.$value.'</span>';
        }

        return $listoption;
    }
}
