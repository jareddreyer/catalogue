<?php
class VideoPage extends Page
{
	private static $allowed_children = array();

}

class VideoPage_Controller extends Page_Controller
{
    private $id, $member;
    
     private static $allowed_actions = array('getKeywords', 'jared');
    
    public function init()
    {
        parent::init();
        $this->id = (int)Controller::curr()->getRequest()->param('ID');
        ($this->id) ? $this->member = $this->id : $this->member = Member::currentUserID();
    }

	public function movies()
	{
	    Requirements::css('themes/simple/css/jplist.core.min.css');
        Requirements::css('themes/simple/css/jplist.textbox-filter.min.css');
        
        $keywords = $this -> getKeywords();
        Requirements::customScript('
        
            var availableKeywords = [
            '.$keywords.'
            ];
            $(function() {
     
                $(".keywordsText").autocomplete({
                    source: availableKeywords,
                    minLength: 3,
                    select: function(event, ui) {
                        $(".keywordsText").trigger("input");
                    },
             
                    html: true, // optional (jquery.ui.autocomplete.html.js required)
                    
                });
             
            });
         ');


	    $sqlQuery = "SELECT catalogue.*, member.ID as MID, member.Email, member.FirstName, member.Surname 
                     FROM catalogue 
                     LEFT JOIN member ON catalogue.Owner = member.ID 
                     WHERE catalogue.Video_type = 'film' 
                     AND catalogue.Owner = $this->member
                     ORDER BY catalogue.Video_title"
                     ;
                     
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
    
    /**
     * gets keywords as a separate query
     */
    public function getKeywords()
    {
        $result = Catalogue::get()->sort('Keywords')->where('keywords is not null')->column($colName = "keywords");                                
        
        if($result != null)
        {
            
            /** clean up keywords from DB **/
            $implode = implode(",", $result); //implode array to string, saves foreaching
            $trim = preg_replace('/\s*,\s*/', ',', $implode); //remove white spaces before and after commas 
            $explode = explode(",", $trim); //explode string to array by comma
            $_list = array(array_keys(array_flip($explode)));  //get only unique elements
            
            $listoption = "";
            foreach($_list as $list)
            {
                foreach ($list as $value)
                {
                    $listoption .= '"'. $value.'",';
                }
            }
                
            return $listoption;
        }
    }
    
    
    /**
     * gets genres as a separate query
     */
    public function getGenres()
    {
        $result = Catalogue::get()->sort('Genre')->where('Genre is not null')->column($colName = "Genre");                                
        
        if($result != null)
        {
            
            /** clean up keywords from DB **/
            $implode = implode("|", $result); //implode array to string, saves foreaching
            $trim = preg_replace('/\s+/', '', $implode); //remove white spaces before and after commas 
            $explode = explode("|", $trim); //explode string to array by comma
            sort($explode); //sort the array alphabetically
            $_list = array(array_keys(array_flip($explode)));  //get only unique elements
            
            
            $genreList = "";
            foreach($_list as $list)
            {
                foreach ($list as $value)
                {
                    $genreList .= "<li><span data-path=\".".$value."\">".$value."</span></li>";    
                }
            }
                
            return $genreList;
        }
    }
    
    /**
     * returns count of titles in catalogue]
     * 
     */
    public function countTitles()
    {
        $count = DB::query("SELECT count(Video_title) FROM Catalogue WHERE catalogue.Owner =".$this->member)->value();
        
        return $count;
    }
    
}

