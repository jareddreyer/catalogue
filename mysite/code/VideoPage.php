<?php
class VideoPage extends Page
{
	private static $allowed_children = array();

}

class VideoPage_Controller extends Page_Controller
{
     private $id, $member, $members;
     private static $allowed_actions = array('getKeywords');
    
    public function init()
    {
        parent::init();
        $this->id = (int)Controller::curr()->getRequest()->param('ID');
        ($this->id) ? $this->member = $this->id : $this->member = Member::currentUserID();
        
        $this -> members = parent::__getAllMembers(); // get all the members
        
        //jplist css
        Requirements::css('themes/simple/css/jplist.core.min.css');
        Requirements::css('themes/simple/css/jplist.textbox-filter.min.css');
    }

	public function movies()
	{
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

        // main SQL call
	    $sqlQuery = "SELECT catalogue.*, member.ID as MID, member.Email, member.FirstName, member.Surname 
                     FROM catalogue 
                     LEFT JOIN member ON catalogue.Owner = member.ID 
                     WHERE catalogue.Video_type = 'film' 
                     AND catalogue.Owner = $this->member
                     ORDER BY catalogue.Video_title";
                     
        $records = DB::query($sqlQuery);
        
        if ($records)
        {
            $set = new ArrayList();
            
            foreach ($records as $record)
            {
                $record['lastupdatedreadable'] = parent::humanTiming($record['LastEdited']);
                $record['genres'] = $this->listFilmGenres($record['Genre']);
                
                $set->push(new ArrayData($record));
            }
            return $set;
        }
        
	}

    /**
     * Takes genres string element and splits them into array element for each genre
     *  
     * @param string
     * @return string
     */
    private function listFilmGenres ($genre)
    {
        $explode = explode("|", $genre); //explode string to array by delimiter
        
        $listoption = "";
        foreach ($explode as $value)
        {
            $listoption .= '<span class="hide genre '.str_replace(' ', '', $value).'">'.$value.'</span>';
        }
        
        return $listoption;
        
    }
    
    /**
     * gets keywords as a separate query
     * sorts and removes duplicates
     * @return array
     */
    public function getKeywords()
    {
        $result = Catalogue::get()->sort('keywords')->where('keywords is not null')->column($colName = "keywords");                                
        
        if($result != null)
        {
            
            /** clean up keywords from DB **/
            $_list = array(parent::__convertAndCleanList($result, ','));
            
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
     * gets genres as a separate query sorts and removes duplicates
     * 
     * @return string
     */
    public function getGenres()
    {
        $result = Catalogue::get()->sort('Genre')->where('Genre is not null')->column($colName = "Genre");                                
        
        if($result != null)
        {
            
            /** clean up keywords from DB **/
            $_list = array(parent::__convertAndCleanList($result, '|'));
            
            $genreList = "";
            foreach($_list as $list)
            {
                foreach ($list as $value)
                {
                    $genreList .= "<li><span data-path=\".".str_replace(' ', '', $value)."\">".$value."</span></li>";
                }
            }
                
            return $genreList;
        }
    }
    
    /**
     * returns count of titles in catalogue
     * 
     * @return string
     */
    public function countTitles()
    {
        $count = DB::query("SELECT count(Video_title) FROM Catalogue WHERE catalogue.Owner =".$this->member)->value();
        
        return $count;
    }
    
    /**
     * returns $this->members from parent::__getAllMembers
     * 
     * @return object
     * 
     */
    public function getMembers()
    {
        return $this->members;
    }
    
}

