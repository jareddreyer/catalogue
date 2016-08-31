<?php
class Page extends SiteTree {

	private static $db = array(
	);

	private static $has_one = array(
	);

}
class Page_Controller extends ContentController {

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
	private static $allowed_actions = array (
	);

	public function init() {
		parent::init();

		// Note: you should use SS template require tags inside your templates 
		// instead of putting Requirements calls here.  However these are 
		// included so that our older themes still work
	}
    
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

}
