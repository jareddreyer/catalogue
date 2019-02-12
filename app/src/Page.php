<?php
class Page extends SiteTree {
//nothing needed here
}

class Page_Controller extends ContentController
{
	private static $allowed_actions = [];

	public function init() {
		parent::init();
        
		Requirements::css('app/css/homepage.css');

        if (Director::isLive()){            
            Requirements::css('app/thirdparty/bootstrap/css/bootstrap.min.css');
            Requirements::javascript('app/thirdparty/bootstrap/js/bootstrap.bundle.min.js');
        } else {
            Requirements::css('app/thirdparty/bootstrap/css/bootstrap.css');
            Requirements::javascript('app/thirdparty/bootstrap/js/bootstrap.bundle.js');
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
	}
    
    /**
     * Grabs all members in the database and returns ID, Firstname & Surname
     * and inserts the profile URL based on getProfileURL()
     *
     * @param $class <string>
     * @return object
     */
    public function __getAllMembers()
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
        $trimmed = array_walk($csv, create_function('&$csv', '$csv = trim($csv);'));
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
    public function getProfileURL () 
    {
        return ProfilePage::get()->first()->URLSegment;
    }  

}
