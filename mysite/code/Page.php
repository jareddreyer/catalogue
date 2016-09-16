<?php
class Page extends SiteTree {

	private static $db = array(
	);

	private static $has_one = array(
	);

}
class Page_Controller extends ContentController
{

	/**
	 * An array of actions that can be accessed via a request. Each array element should be an action name, and the
	 * permissions or conditions required to allow the user to access it.
	 *
	 * <code>
	 * array (
	 *     'action', // anyone can access this action
	 *     'action' => true, // same as above
	 *     'action' => 'ADMIN', // you must have ADMIN permissions to access this action
	 *     'action' => '->checkAction' // you can only access this action if $this->checkAction() returns true
	 * );
	 * </code>
	 *
	 * @var array
	 */
	private static $allowed_actions = array ();

	public function init() {
		parent::init();

		// Note: you should use SS template require tags inside your templates 
		// instead of putting Requirements calls here.  However these are 
		// included so that our older themes still work
		
		Requirements::css('themes/simple/css/homepage.css');
        
        Requirements::customScript('
                 $("a.scroll-arrow").hover( 
               function() {
                   var container = $(this).parent().attr("id");
                   console.log(container)
                   var direction = $(this).is("#scroll-right") ? "+=" : "-=";
                   var totalWidth = -$(".row__inner").width();
                   $(".row__inner .tile").each(function() {
                       totalWidth += $(this).outerWidth(true);
                   });
                   $("#"+ container + " .row__inner").animate({
                       scrollLeft: direction + Math.min(totalWidth, 3000)
                       
                   },{
                       duration: 2000,
                       easing: "swing", 
                       queue: false }
                   );
               },
            function() {
                 $(".row__inner").stop();
            }
        );');
	}
    
    /**
     * Grabs all members in the database and returns ID, Firstname & Surname
     * 
     * @return object
     */
    public function __getAllMembers()
    {
        $members = DataObject::get("Member")->sort('FirstName')->setQueriedColumns(array("ID", "FirstName", "Surname"))->exclude('ID', 1);
        
        return $members;
    }
    
    
    /**
     * Gets all recently added titles
     * 
     * @return array
     */
    public function recentlyAddedTitles()
    {
        // main SQL call
        $sqlQuery = "SELECT 
                            c.ID,
                            c.LastEdited,
                            c.Video_title,
                            c.Source,
                            c.Quality,
                            c.Year,
                            c.Status,
                            c.Poster,                            
                            member.Email,
                            member.FirstName,
                            member.Surname 
                     FROM catalogue as c
                     LEFT JOIN member ON c.Owner = member.ID 
                     WHERE c.LastEdited IS NOT NULL
                     AND c.Created = c.LastEdited
                     ORDER BY c.LastEdited DESC
                     LIMIT 12";
        
        $records = DB::query($sqlQuery);
        
        if ($records)
        {
            $set = new ArrayList();
            
            foreach ($records as $record)
            {
                $record['lastupdatedreadable'] = $this->humanTiming($record['LastEdited']);
                
                $set->push(new ArrayData($record));
            }
            
            return $set;
        }

    }
    
     /**
     * Gets all recently added titles
     * 
     */
    public function recentlyUpdatedTitles()
    {
        // main SQL call
        $sqlQuery = "SELECT 
                            c.ID,
                            c.LastEdited,
                            c.Video_title,
                            c.Source,
                            c.Quality,
                            c.Year,
                            c.Status,
                            c.Poster,                            
                            member.Email,
                            member.FirstName,
                            member.Surname 
                     FROM catalogue as c
                     LEFT JOIN member ON c.Owner = member.ID 
                     WHERE c.LastEdited IS NOT NULL
                     AND c.LastEdited > c.Created 
                     ORDER BY c.LastEdited DESC
                     LIMIT 12";
        
        $records = DB::query($sqlQuery);
        
        if ($records)
        {
            $set = new ArrayList();
            
            foreach ($records as $record)
            {
                $record['lastupdatedreadable'] = $this->humanTiming($record['LastEdited']);
                
                $set->push(new ArrayData($record));
            }
            
            return $set;
        }

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
     * @param array
     * @return array
     * 
     */
    public function __convertAndCleanList($array, $pipe)
    {
        /** clean up keywords from DB **/
        
        $implode = implode($pipe, $array); //implode array to string, saves foreaching
        $csv = str_getcsv($implode, $pipe);
        $trimmed = array_walk($csv, create_function('&$csv', '$csv = trim($csv);'));
        $unique = array_keys(array_flip($csv));  //get only unique elements

        return $unique;
    }

}
