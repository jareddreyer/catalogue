<?php
class VideoPage extends Page
{
	private static $allowed_children = array();

}

class VideoPage_Controller extends Page_Controller
{

	public function movies()
	{
	    
	    $sqlQuery = "SELECT catalogue.*, member.ID as MID, member.Email, member.FirstName, member.Surname 
                     FROM catalogue 
                     LEFT JOIN member ON catalogue.Owner = member.ID 
                     WHERE catalogue.Video_type = 'Movie'
					 ORDER BY catalogue.Video_title";
                     
        $records = DB::query($sqlQuery);             
        
        //debug::dump($records->value());

        if ($records)
        {
            $set = new ArrayList();
            foreach ($records as $record)
            {
                $record['lastupdatedreadable'] = parent::humanTiming($record['Last_updated']);
                $set->push(new ArrayData($record));
            }
            return $set;
        }
	}
	
	public function index()
	{
		
		$action = Controller::curr()->getRequest()->param('action');

		if ($action)
		{	
			return $this->renderWith('blankResults');
		} else {
			return array();
		}
		
    }
    
}

