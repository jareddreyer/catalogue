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
            DB::alteration_message("Deleting 'about us' & 'contact us' paghes", 'deleted');
        }
    }
}

class Page_Controller extends ContentController
{
    public $member, $id, $apiKey, $postersAssetsFolderName, $jsonAssetsFolderName, $jsonPath, $postersPath;

	public function init() {
		parent::init();

		Requirements::themedCSS('homepage');

        Requirements::javascript("https://code.jquery.com/jquery-1.12.4.min.js");

        Requirements::themedJavascript("jquery-ui-1.10.4.custom.min");
        Requirements::themedJavascript("bootstrap.min");
        Requirements::themedJavascript("tag-it.min");
        Requirements::themedJavascript("jplist.core.min");
        Requirements::themedJavascript("jplist.pagination-bundle.min");
        Requirements::themedJavascript("jplist.filter-dropdown-bundle.min");
        Requirements::themedJavascript("jplist.textbox-filter.min");
        Requirements::themedJavascript("jplist.history-bundle.min");

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
        $this->id = (int)Controller::curr()->getRequest()->param('ID');

        // check if slug is set, if not then use currentMember()
        (!$this->id) ? $this->id = $this->member : $this->id;

        // get config variables
        $this->apiKey = Config::inst()->get('Catalog', 'apiKey');
        $this->postersAssetsFolderName = Config::inst()->get('Catalog', 'postersAssetsFolderName');
        $this->jsonAssetsFolderName = Config::inst()->get('Catalog', 'jsonAssetsFolderName');
        $this->jsonPath = ASSETS_PATH . $this->jsonAssetsFolderName;
        $this->postersPath = ASSETS_PATH . $this->postersAssetsFolderName;




	}

    /**
     * Grabs all members in the database and returns ID, Firstname & Surname
     * and inserts the profile URL based on getProfileURL()
     *
     * @param $class <string>
     * @return object
     */
    public function getAllMembers()
    {
        $members = Member::get()->sort('FirstName')->setQueriedColumns(array("ID", "FirstName", "Surname"))->exclude('ID', 1);

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
     * Returns object for either newly added titles or
     * updated titles
     *
     * @param  string $type - 'added' or 'updated'
     *
     * @return object - titles
     */
    public function recentTitles ($type)
    {
        if ($type == 'added')
            return Catalogue::get()->where('LastEdited is not null AND Created = LastEdited')->sort('LastEdited DESC');
        if ($type == 'updated')
            return Catalogue::get()->where('LastEdited is not null AND LastEdited > Created')->sort('LastEdited DESC');
    }

    /**
     *
     * @param string
     *
     * returns string in human readable time
     *
     * @return string
     *
     */
    public function humanTiming($time)
    {

            $currtime = time();

            $ago = abs($currtime - strtotime($time));

            if($ago < 60 ) {
                $result = 'less than a minute';
            } elseif($ago < 3600) {
                $span = round($ago/60);
                $result = ($span != 1) ? "{$span} ". "mins" : "{$span} ". "min";
            } elseif($ago < 86400) {
                $span = round($ago/3600);
                $result = ($span != 1) ? "{$span} ". "hours" : "{$span} ". "hour";
            } elseif($ago < 86400*30) {
                $span = round($ago/86400);
                $result = ($span != 1) ? "{$span} ". "days" : "{$span} ". "day";
            } elseif($ago < 86400*365) {
                $span = round($ago/86400/30);
                $result = ($span != 1) ? "{$span} ". "months" : "{$span} ". "month";
            } elseif($ago > 86400*365) {
                $span = round($ago/86400/365);
                $result = ($span != 1) ? "{$span} ". "years" : "{$span} "."year";
            }

            // Replace duplicate spaces, backwards compat with existing translations
            $result = preg_replace('/\s+/', ' ', $result);

            return $result;
    }

    /**
     * takes an array list and cleans it up ready to output as unique string
     *
     * @param $array <array>, $pipe <string>
     * @return array
     *
     */
    public function convertAndCleanList($array, $pipe)
    {
        /** clean up keywords from DB **/

        $implode = implode($pipe, $array); //implode array to string, saves foreaching
        $csv = str_getcsv($implode, $pipe);
        $trimmed = array_walk($csv, function(&$csv){ return $csv = trim($csv); } );
        $unique = array_keys(array_flip($csv));  //get only unique elements

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
     * @return string
     */
    public function countTitles()
    {
        $id = (int)Controller::curr()->getRequest()->param('ID');
        (!$id) ? $id = Member::currentUserID() : $id ;

        if($count = Catalogue::get()->filter(['VideoType'=>'film', 'Owner'=>$id])->count()) {
            return $count;
        }

        return false;
    }

}
