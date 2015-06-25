<?php
class VideoTelevisionPage extends Page
{
    private static $allowed_children = array();

}

class VideoTelevisionPage_Controller extends Page_Controller
{

    public function television()
    {
        
        $sqlQuery = "SELECT catalogue.*, member.ID AS MID, member.Email, member.FirstName, member.Surname 
                     FROM catalogue 
                     LEFT JOIN member ON catalogue.Owner = member.ID 
                     WHERE catalogue.Video_type = 'TV' OR catalogue.Video_type = 'web'
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
    
    
    
    public function tvjsonresults()
    {
        return;
    }

}