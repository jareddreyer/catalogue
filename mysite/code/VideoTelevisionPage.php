<?php
class VideoTelevisionPage extends Page
{
    private static $allowed_children = array();

}

class VideoTelevisionPage_Controller extends Page_Controller
{

    public function television()
    {
        Requirements::css('themes/simple/css/jplist.core.min.css');
        Requirements::css('themes/simple/css/jplist.textbox-filter.min.css');
        
        $id = (int)Controller::curr()->getRequest()->param('ID');
        ($id) ? $member = $id : $member = Member::currentUserID();
        
        $sqlQuery = "SELECT catalogue.*, member.ID AS MID, member.Email, member.FirstName, member.Surname 
                     FROM catalogue 
                     LEFT JOIN member ON catalogue.Owner = member.ID 
                     WHERE catalogue.Video_type = 'series'
                     AND catalogue.Owner = $member
                     ORDER BY catalogue.Video_title";
                     
        $records = DB::query($sqlQuery);             
        
        //debug::dump($records->value());

        if ($records)
        {
            $set = new ArrayList();
            
            foreach ($records as $record)
            {
                $record['lastupdatedreadable'] = parent::humanTiming($record['Last_updated']);
                $record['seasonLinks'] = str_replace('Season', '', $record['Seasons']);
                $set->push(new ArrayData($record));
            }
            return $set;
        }

    }

    /**
     * returns count of titles in catalogue]
     * 
     */
    public function countTitles()
    {
        $id = (int)Controller::curr()->getRequest()->param('ID');
        ($id) ? $member = $id : $member = Member::currentUserID();
        
        $count = DB::query("SELECT count(Video_title) FROM Catalogue WHERE Owner =".$member . " AND Video_type = 'series'")->value();
        
        return $count;
    }
    
}