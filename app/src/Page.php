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
        Folder::find_or_make(Config::inst()->get('Catalogue', 'postersAssetsFolderName'));
        Folder::find_or_make(Config::inst()->get('Catalogue', 'jsonAssetsFolderName'));
    }
}

class Page_Controller extends ContentController
{
    public $member, $slug, $postersAssetsFolderName, $jsonAssetsFolderName, $jsonPath, $postersPath;

    public static $OMDBAPIKey = OMDBAPIKey;
    public static $TMDBAPIKey = TMDBAPIKey;

    public static $genresDefaultList = [
        "Comedy", "Drama", "Horror", "Science Fiction", "Comic/Super Heroes", "Action", "Thriller",
        "Crime", "Documentary", "Family", "Animated", "Romance", "Adventure", "War", "Sitcom"
    ];

    private static $allowed_actions = [
        'getComments',
        'handleComment'
    ];

    private static $url_handlers = [
        'comments/$ID' => 'getComments'
    ];

    public function init()
    {
		parent::init();

        Requirements::customScript('let OMDBAPIKey = \''.self::$OMDBAPIKey.'\'');

        $themeDir = $this->ThemeDir();
        Requirements::set_write_js_to_body(true);
        Requirements::set_force_js_to_bottom(true);
        Requirements::set_combined_files_folder($themeDir.'/dist');

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
            Requirements::themedJavascript('jplist.bootstrap-filter-dropdown.min');
            Requirements::themedJavascript('jplist.filter-dropdown-bundle.min');
            Requirements::themedJavascript('jplist.filter-toggle-bundle.min');
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
        $this->slug = (int)Controller::curr()->getRequest()->param('ID');

        // we have a currentUserID and no slug
        if($this->member !== 0 && $this->slug === 0)
        {
            $this->slug = $this->member;
        }

        // we have a slug and no currentUserID
        if($this->member === 0 && $this->slug !== 0)
        {
            $this->member = $this->slug;
        }

        // get config variables
        $this->postersAssetsFolderName = Config::inst()->get('Catalogue', 'postersAssetsFolderName');
        $this->jsonAssetsFolderName = Config::inst()->get('Catalogue', 'jsonAssetsFolderName');
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
     * Creates filter of $Type by 'Owner', and video 'Type', then returns as string ready
     * for inclusion on the JpList panel.
     *
     * If the $outputType flag is set then it will filter by all video 'Type's and output as a json_encoded string.
     *
     * This function also sorts and removes duplicates
     *
     * @param string $ClassName - Used to set type ov media lookup
     * @param $filter <string> - Select Genres or Keywords.
     * @param mixed $outputType - jplist filter or tagit filter e.g. 'json' - Default is null ('html')
     * @return string|void
     */
    public function getMetadataFilters($ClassName, $filter, $outputType = null)
    {
        $result = Catalogue::get();

        switch ($ClassName){
            case 'FilmsPage':
                $type = 'movie';
                break;
            case 'TelevisionPage':
                $type = 'series';
                break;
            case 'MaintenanceFormPage':
                $type = ['movie', 'series'];
                break;
        }

        if($filter == 'Keywords') {
            $filtersResult = $result->filter(
                [
                    'Type'         => $type,
                    'OwnerID'      => $this->member,
                    'Keywords:not' => ''
                ]
            )->column('Keywords');
        }

        if($filter == 'Genre') {
            $filtersResult = $result->filter(
                [
                    'Type'      => $type,
                    'OwnerID'   => $this->member,
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

                if($outputType == 'javascript') {
                    array_walk($filterArr, function(&$filterArr){ return $filterArr = json_encode($filterArr); } );
                    $filtersList = implode(',', $filterArr);

                } else {
                    foreach ($filterArr as $filters) {
                        $filtersList->push(ArrayData::create(
                            [
                                'filters' =>
                                    '<input id="'.$filters.'" data-path=".'.$this->filterSafeCSS($filters).'" type="checkbox">'."\n\r".
                                    '<label for="'.$filters.'">'.$filters.' '.
                                    '<span
                                         data-control-type="counter"
                                         data-control-action="counter"
                                         data-control-name="'.$filters.'-counter"
                                         data-format="({count})"
                                         data-path=".'.$this->filterSafeCSS($filters).'"
                                         data-mode="all"
                                         data-type="path"></span>'.
                                    '</label>'
                            ]
                        ));
                    }
                }

                return $filtersList;
            }
        }

        return;
    }

    /**
     * Takes string from either Genres or Keywords field and splits them into array element
     * then returns back as a string for display in frontend as individual <span> items.
     *
     * @param string $field - value of Genre or Keywords
     * @param string $classes - adds a css class name to the string
     * @return string
     */
    public function getFieldFiltersList($field, $classes)
    {
        if($explode = explode(",", $field))
        {
            $listoption = '';

            foreach ($explode as $value)
            {
                $listoption .= '<span class="'.$classes.' '.$this->filterSafeCSS($value).'">'.$value.'</span> ';
            }
            return $listoption;
        }

        return;
    }

    /**
     * Helper method to remove special chars and numbers out of metadata fields
     * making it safe for css selectors
     *
     * @param string $string
     * @return string|string[]|null
     */
    public function filterSafeCSS(string $string)
    {
        return preg_replace("/[^a-zA-Z]/", '', $string);
    }

    /**
     * This function is an ajax request that returns comments posted to a film or series
     * as a json object back to the view.
     *
     * @param SS_HTTPRequest $request
     * @return string $comments
     */
    public function getComments(SS_HTTPRequest $request)
    {
        if(Director::is_ajax()) {
            $comments = Comment::get()->filter('CatalogueID', $request->param('ID'));

            if($comments->exists()) {
                $pageinatedComments = PaginatedList::create($comments, $request)
                ->setPageLength(3)->setPaginationGetVar('comments');

                $commentsList = ArrayList::create();

                foreach ($pageinatedComments as $comment){
                    // clean up date
                    $date = new SS_Datetime();
                    $date->setValue($comment->Created);

                    // get author object ready to be stringified
                    $author = DataObject::get_by_id(Member::class, $comment->AuthorID);

                    $comment->Created = $date->Ago();
                    $comment->Author = '<a href="'.$this->Link(). 'user/'. $comment->AuthorID .'">'. $author->FirstName . '</a>';
                    $commentsList->push($comment);
                }

                // now wrap it in a comments array so total items can be separate
                $commentsJson = [
                        'CommentsCount' =>
                                     [
                                         'TotalItems' => $pageinatedComments->getTotalItems(),
                                         'TotalPages' => $pageinatedComments->TotalPages(),
                                         'CurrentPage' =>$pageinatedComments->getPageStart()
                                     ]
                ];
                $commentsJson['Comments'] = $commentsList->toNestedArray();

                return json_encode($commentsJson);
            } else {

                return json_encode(null);
            }
        }

        return $this->redirect($this->Link);
    }

    /**
     * Returns single comment record for use in an ajax request.
     *
     * @param int $commentID
     * @return false|string|void
     */
    public function getComment(int $commentID)
    {
        $comment = DataObject::get_by_id(Comment::class, $commentID);

        if($comment->exists()) {
            $commentsList = ArrayList::create();

            // get author object ready to be stringified
            $author = DataObject::get_by_id(Member::class, $comment->AuthorID);

            // clean up date
            $createdDate = new SS_Datetime();
            $createdDate->setValue($comment->Created);

            $comment->Created = $createdDate->Ago();
            $comment->Author = '<a href="'.$this->Link(). 'user/'. $comment->AuthorID .'">'. $author->FirstName . '</a>';
            $commentsList->add($comment);

            return json_encode($commentsList->toNestedArray());
        } else {

            return json_encode(null);
        }

        return;
    }

    public function commentForm()
    {
        $commentForm = Form::create(
            $this,
            'handleComment',
            FieldList::create(
                TextareaField::create('Comment','')
                    ->setAttribute('Placeholder', 'Comment*')
                    ->addExtraClass('comment-field')
                    ->setRows(0)
                    ->setColumns(0),
                HiddenField::create('CatalogueID', '')->addExtraClass('catalogueID')
            ),

            FieldList::create(
                FormAction::create('handleComment', 'Post')
                    ->setUseButtonTag(true)
                    ->setAttribute('url', $this->Link() . 'handleComment')
                    ->addExtraClass('btn btn-primary')
            ),

            RequiredFields::create('Comment')
        )->addExtraClass('commentForm');

        return $commentForm;
    }

    public function handleComment($data)
    {
        if(Director::is_ajax()) {
            $comment = Comment::create();
            $comment->update(
                [
                    'AuthorID' => $this->member,
                    'CatalogueID' => (int)$data['CatalogueID'],
                    'Comment' => $data['Comment']
                ]
            );
            $comment->write();

            if ($comment->ID != null)
            {
                return $this->getComment($comment->ID);
            } else {
                return false;
            }
        }

        // no ajax request so return 404 not found
        return $this->httpError(404, 'Not Found');
    }
}
