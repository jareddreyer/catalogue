<?php
class TelevisionPage extends Page
{
    private static $allowed_children = array();

}

class TelevisionPage_Controller extends Page_Controller
{
    private static $allowed_actions = array (
        'television'
    );

    private static $url_handlers = array(
        'user/$ID' => 'television'
    );

    public function init()
    {

        parent::init();
        $this->id = (int)Controller::curr()->getRequest()->param('ID');
        ($this->id) ? $this->member = $this->id : $this->member = Member::currentUserID();
           
        
        //JPlist css
        Requirements::css('themes/simple/css/jplist.core.min.css');
        Requirements::css('themes/simple/css/jplist.textbox-filter.min.css');
        
    }

    public function television()
    {
        
        $keywords = $this -> getKeywords();

        Requirements::customScript('
        
            var availableKeywords = [
            '. $keywords .'
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
        
        $sqlQuery = "SELECT Catalogue.*, Member.ID AS MID, Member.Email, Member.FirstName, Member.Surname 
                     FROM Catalogue 
                     LEFT JOIN Member ON Catalogue.Owner = Member.ID 
                     WHERE Catalogue.Video_type = 'series'
                     AND Catalogue.Owner = $this->member
                     ORDER BY Catalogue.Video_title";
                     
        $records = DB::query($sqlQuery);

        if ($records)
        {
            $set = ArrayList::create();
            
            foreach ($records as $record)
            {
                $record['lastupdatedreadable'] = parent::humanTiming($record['LastEdited']);
                $record['seasonLinks'] = str_replace('Season', '', $record['Seasons']);
                $record['genres'] = $this->listFilmGenres($record['Genre']);
                $record['posters'] = POSTERSWEBPATH;
                $record['profileLink'] = parent::getProfileURL()->URLSegment;
                
                $set->push(ArrayData::create($record));
            }

            //return $set;
            return $this->customise(array('television' => $set) );
        }

    }

    /**
     * gets keywords as a separate query
     */
    public function getKeywords()
    {
        $result = Catalogue::get()->sort('Keywords')->where('Keywords is not null')->column("Keywords"); 

        if($result != null)
        {
            
            /** clean up keywords from DB **/
            $_list = array(parent::convertAndCleanList($result, ','));

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
     * @param string
     * 
     * @desc takes genres string element and splits them into array element for each genre 
     * 
     * @return array
     */
    private function listFilmGenres ($genre)
    {
         
        $explode = explode("|", $genre); //explode string to array by comma
        
        $listoption = "";
        foreach ($explode as $value)
        {
            $listoption .= '<span class="hide genre '.str_replace(' ', '', $value).'">'.$value.'</span>';
            
        }
        
        return $listoption;
        
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
     * returns count of titles in catalogue
     * 
     */
    public function countTitles()
    {
        $id = (int)Controller::curr()->getRequest()->param('ID');
        ($id) ? $member = $id : $member = Member::currentUserID();
        
        $count = DB::query("SELECT count(Video_title) FROM Catalogue WHERE Owner =".$member . " AND Video_type = 'series'")->value();
        
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
        return parent::__getAllMembers(); // get all the members;
    }
    
    
}