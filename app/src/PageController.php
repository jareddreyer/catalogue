<?php

use App\Catalogue\Models\Catalogue;
use App\Catalogue\Models\Comment;
use App\Catalogue\PageTypes\FilmsPage;
use App\Catalogue\PageTypes\MaintenanceFormPage;
use App\Catalogue\PageTypes\ProfilePage;
use App\Catalogue\PageTypes\TelevisionPage;
use App\Catalogue\Traits\CatalogueTrait;
use SilverStripe\Assets\Image;
use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Environment;
use SilverStripe\Core\Manifest\ModuleResourceLoader;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\TextareaField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\PaginatedList;
use SilverStripe\ORM\Queries\SQLSelect;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\View\ArrayData;
use SilverStripe\View\Requirements;
use SilverStripe\View\ThemeResourceLoader;

class PageController extends ContentController
{

    use CatalogueTrait;

    /**
     * @todo this need typing correctly is it a member object or member ID integer?
     */
    public int|null $member;
    public int|null $slug;
    public string $postersFolderName = 'posters';
    public string $metadataFolderName = 'metadata';

    public static string $OMDBAPIKey;
    public static string $TMDBAPIKey;

    public static array $genresDefaultList = [
        'Comedy', 'Drama', 'Horror', 'Science Fiction', 'Comic/Super Heroes', 'Action', 'Thriller',
        'Crime', 'Documentary', 'Family', 'Animated', 'Romance', 'Adventure', 'War', 'Sitcom',
    ];

    private static array $allowed_actions = [
        'getComments',
        'poster',
    ];

    private static array $url_handlers = [
        'comments/$ID' => 'getComments',
    ];

    protected function init()
    {
        parent::init();

        self::$OMDBAPIKey = Environment::getEnv('METADATA_API_KEY');
        self::$TMDBAPIKey = Environment::getEnv('TRAILERS_API_KEY');

        Requirements::customScript('let OMDBAPIKey = \''.self::$OMDBAPIKey.'\'');

        $themeDir = ThemeResourceLoader::inst()->getThemePaths()[1];

        Requirements::set_write_js_to_body(true);
        Requirements::set_force_js_to_bottom(true);

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
                $themeDir . '/css/homepage.css',
            ]
        );

        Requirements::javascript('//code.jquery.com/jquery-1.12.4.min.js');
        Requirements::combine_files('app.js', [
            $themeDir . '/javascript/jquery-ui-1.10.4.custom.min.js',
            $themeDir . '/javascript/bootstrap.min.js',
            $themeDir . '/javascript/tag-it.min.js',
        ]);

        // @todo refactor when jplist-es6 has same functionality as jquery jplist
        if ($this->ClassName === FilmsPage::class|| $this->ClassName === TelevisionPage::class) {
            Requirements::themedCSS('jplist.core.min');
            Requirements::themedCSS('jplist.textbox-filter.min');
            Requirements::themedCSS('jplist.filter-toggle-bundle.min');
            Requirements::themedCSS('jplist.checkbox-dropdown.min');
            Requirements::themedJavascript('jplist.core.min');
            Requirements::themedJavascript('jplist.pagination-bundle.min');
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

        $this->member = null;

        if (Security::getCurrentUser()) {
            $this->member = $this->getMember()->ID;
        }

        $slug = Controller::curr()->getRequest()->param('ID');
        $this->slug = Controller::curr()->getRequest()->param('ID') ? (int)$slug : null;

        // Set up routing slugs
        // User(s) need to be logged in to view a catalogue.
        // We have a currentUserID and no slug
        if ($this->member && $this->slug === null) {
            $this->slug = $this->member;
        }

        // we have a slug and no currentUserID
        if ($this->member === null && $this->slug !== null) {
            $this->member = $this->slug;
        }
    }

    /**
     * Grabs all members in the database and returns ID, Firstname & Surname
     * Used in templates.
     */
    public function getAllMembers(): DataList|null
    {
        $members = Member::get()->sort('FirstName')
            ->setQueriedColumns(
                ['ID', 'FirstName', 'Surname']
            )
            ->exclude('ID', 1);

        if ($members->count() > 1) {
            return $members;
        }

        return null;
    }

    /**
     * Returns Member object so can we can iterate its object for,
     * Firstname and Lastname of users catalog.
     */
    public function getMember(): DataObject|null
    {
        $member = Security::getCurrentUser();

        if ($member instanceof Member) {
            return $member;
        }

        return null;
    }

    /**
     * Returns object for either newly added titles or
     * updated titles
     *
     * @param string $type - 'added' or 'updated'
     */
    public function recentTitles(string $type): ArrayList|null
    {
        if ($type !== 'added' && $type !== 'updated') {
            return null;
        }

        // Grab our Catalogue table model.
        $schema = DataObject::getSchema();
        $catalogueTable = DB::get_conn()->escapeIdentifier($schema->baseDataTable(Catalogue::class));

        if ($type === 'added') {
            $addedTitles = new SQLSelect();
            $addedTitles->setFrom($catalogueTable);
            $addedTitles->setWhere('Created BETWEEN (CURRENT_DATE() - INTERVAL 1 MONTH) AND CURRENT_DATE()');
            $addedTitles->setLimit(15);
            $addedTitles->setOrderBy('Created DESC');

            $result = $addedTitles->execute();
        }

        if ($type === 'updated') {
            $recentlyUpdatedTitles = new SQLSelect();
            $recentlyUpdatedTitles->setFrom($catalogueTable);
            $recentlyUpdatedTitles->setWhere('LastEdited is not null AND LastEdited > Created');
            $recentlyUpdatedTitles->setLimit(15);
            $recentlyUpdatedTitles->setOrderBy('LastEdited DESC');

            $result = $recentlyUpdatedTitles->execute();
        }

        $list = ArrayList::create();

        foreach ($result as $title) {
            $list->push($title);
        }

        return $list;
    }

    /**
     * takes an array list and cleans it up ready to output as unique string
     * and sorts alphabetically.
     * @return string[]
     */
    public function convertAndCleanList(string $item, string $pipe): array
    {
        $implode = explode($pipe, $item);

        if (!$implode) {
            return [];
        }

        $csv = str_getcsv($item ?? $item, $pipe);

        array_walk($csv, function (&$csv) {
            return $csv = trim($csv);
        });
        $unique = array_keys(array_flip($csv)); //get only unique elements
        sort($unique, SORT_FLAG_CASE | SORT_STRING);

        return $unique;
    }

    /**
     * Returns the link to the profile page type
     * which allows us to dynamically create the url to be linked from
     * as opposed to using static hardcoded paths
     */
    public function getProfileURL(): string
    {
        $page = ProfilePage::get()->first();

        if (!$page) {
            return '';
        }

        return Controller::join_links($page->Link());
    }

    /**
     * Returns the MaintenanceFormPage class link
     * used by most pages so handy to reuse a method helper.
     */
    public function getMaintenanceFormPageLink(): string
    {
        $page = MaintenanceFormPage::get()->first();

        if (!$page) {
            return '';
        }

        return $page->Link();
    }

    /**
     * returns count of titles in catalogue by member
     */
    public function getCountTitles(string $type): bool|string
    {
        if ($count = Catalogue::get()->filter(['Type' => $type, 'OwnerID' => $this->slug])->count()) {
            return $count;
        }

        return false;
    }

    /**
     * Checks if we have a poster saved to assets already
     */
    public function getPosterImageByFilename(string $filename): bool|DataObject
    {
        $poster = Image::get()->filter('Name', $filename)->first();

        return ($poster === null) ? false : $poster;
    }

    public function getPosterImageByCatalogueSlug(): DataObject|string
    {
        $this->slug = $this->getCatalogueSlug();

        if ($this->slug !== 0) {
            $posterId = Catalogue::get_by_id($this->slug)->PosterID;

            if ($posterId) {
                return Image::get_by_id($posterId);
            }
        }

        // Found nothing so returning blank.
        return ModuleResourceLoader::resourceURL('themes/app/images/blank.png');
    }

    public function getCatalogueSlug(): int
    {
        $request = Controller::curr()->getRequest();

        return $request->params()['ID'] ?? 0;
    }

    /**
     * Creates filter of $Type by 'Owner', and video 'Type', then returns as string ready
     * for inclusion on the JpList panel.
     *
     * If the $outputType flag is set then it will filter by all video 'Type's and output as a json_encoded string.
     *
     * This function also sorts and removes duplicates
     *
     * @todo disabled until I can find a better library for faceted search/filtering/sorting.
     *
     * @param string $ClassName - Used to set type ov media lookup
     * @param $filter <string> - Select Genres or Keywords.
     * @param mixed $outputType - jplist filter or tagit filter e.g. 'json' - Default is null ('html')
     * @return string|void
     * @phpcsSuppress - disabled until I can find new forntend library.
     */
    public function getMetadataFilters(string $ClassName, $filter, $outputType = null)
    {
        return;

        $result = Catalogue::get();

        switch ($ClassName) {
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

        if ($filter === 'Keywords') {
            $filtersResult = $result->filter(
                [
                    'Type' => $type,
                    'OwnerID' => $this->member,
                    'Keywords:not' => '',
                ]
            )->column('Keywords');
        }

        if ($filter === 'Genre') {
            $filtersResult = $result->filter(
                [
                    'Type' => $type,
                    'OwnerID' => $this->member,
                    'Genre:not' => '',
                ]
            )->column('Genre');
        }

        if ($result->exists() === true) {
            if (!empty($filtersResult)) {
                // clean up and return distinct keywords from DB
                $filterArr = self::convertAndCleanList($filtersResult, ',');
                $filtersList = ArrayList::create();

                if ($outputType === 'javascript') {
                    array_walk($filterArr, function (&$filterArr) {
                        return $filterArr = json_encode($filterArr);
                    });
                    $filtersList = implode(',', $filterArr);
                } else {
                    foreach ($filterArr as $filters) {
                        $filtersList->push(ArrayData::create(
                            [
                                'filters' =>
                                    '<input id="'.$filters.'" data-path=".'.$this->filterSafeCSS(
                                        $filters
                                    ).'" type="checkbox">'."\n\r".
                                    '<label for="'.$filters.'">'.$filters.' '.
                                    '<span
                                         data-control-type="counter"
                                         data-control-action="counter"
                                         data-control-name="'.$filters.'-counter"
                                         data-format="({count})"
                                         data-path=".'.$this->filterSafeCSS($filters).'"
                                         data-mode="all"
                                         data-type="path"></span>'.
                                    '</label>',
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
     * @param ?string $field - value of Genre or Keywords
     * @param ?string $classes - adds a css class name to the string, can be empty.
     */
    public function getFieldFiltersList(?string $field, ?string $classes): ?string
    {
        if ($field !== null && $field !== '') {
            $explode = explode(',', $field);

            if ($explode) {
                $listoption = '';

                foreach ($explode as $value) {
                    $listoption .= '<span class="' . $classes . ' ' . $this->filterSafeCSS(
                        $value
                    ) . '">' . $value . '</span> ';
                }

                return $listoption;
            }
        }

        return null;
    }

    /**
     * Helper method to remove special chars and numbers out of metadata fields
     * making it safe for css selectors
     *
     * @param string $string
     * @return string|string[]|null
     */
    public function filterSafeCSS(string $string): string|array|null
    {
        return preg_replace('/[^a-zA-Z]/', '', $string);
    }

    /**
     * This function is an ajax request that returns comments posted to a film or series
     * as a json object back to the view.
     *
     * @param HTTPRequest $request
     * @return string $comments
     */
    public function getComments(HTTPRequest $request): string
    {
        if ($request->isAjax()) {
            $comments = Comment::get()->filter('CatalogueID', $request->param('ID'));

            if ($comments->exists()) {
                $pageinatedComments = PaginatedList::create($comments, $request)
                    ->setPageLength(3)->setPaginationGetVar('comments');

                $commentsList = ArrayList::create();

                foreach ($pageinatedComments as $comment) {
                    // clean up date
                    $date = DBDatetime::create();
                    $date->setValue($comment->Created);

                    // get author object ready to be stringified
                    $author = DataObject::get_by_id(Member::class, $comment->AuthorID);
                    $comment->Created = $date->Ago();
                    $comment->Author = '<a href="' . $this->Link() .
                        'user/' . $comment->AuthorID . '">' . $author->FirstName .
                        '</a>';
                    $commentsList->push($comment);
                }

                // now wrap it in a comments array so total items can be separate
                $commentsJson = [
                    'CommentsCount' => [
                         'TotalItems' => $pageinatedComments->getTotalItems(),
                         'TotalPages' => $pageinatedComments->TotalPages(),
                         'CurrentPage' =>$pageinatedComments->getPageStart(),
                     ],
                ];
                $commentsJson['Comments'] = $commentsList->toNestedArray();

                return json_encode($commentsJson);
            }

            return '[]';
        }

        return $this->redirect($this->Link);
    }

    /**
     * Returns single comment record for use in an ajax request.
     *
     * @todo needs fixing on returns.
     */
    public function getComment(int $commentID): string|null
    {
        $comment = DataObject::get_by_id(Comment::class, $commentID);

        if ($comment->exists()) {
            $commentsList = ArrayList::create();

            // get author object ready to be stringified
            $author = DataObject::get_by_id(Member::class, $comment->AuthorID);

            // clean up date
            $createdDate = DBDatetime::create();
            $createdDate->setValue($comment->Created);

            $comment->Created = $createdDate->Ago();
            $comment->Author = '<a href="'.$this->Link(). 'user/'. $comment->AuthorID .'">'. $author->FirstName . '</a>';
            $commentsList->add($comment);

            return json_encode($commentsList->toNestedArray());
        } else {
            return json_encode(null);
        }

        return null;
    }

    public function commentForm(): Form
    {
        return Form::create(
            $this,
            'handleComment',
            FieldList::create(
                TextareaField::create('Comment', '')
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
    }

    public function handleComment($data)
    {
        if (Director::is_ajax()) {
            $comment = Comment::create();
            $comment->update(
                [
                    'AuthorID' => $this->member,
                    'CatalogueID' => (int)$data['CatalogueID'],
                    'Comment' => $data['Comment'],
                ]
            );
            $comment->write();

            if ($comment->ID !== null) {
                return $this->getComment($comment->ID);
            }

            return false;
        }

        // no ajax request so return 404 not found
        return $this->httpError(404, 'Not Found');
    }

}
