<?php
class VideoProfilePage extends Page
{
    private static $allowed_children = array();
    
}

class VideoProfilePage_Controller extends Page_Controller
{
    public $id, $keywords, $keywordsArr, $video;
    public function init()
    {
        parent::init();
        
        (int) $this->id = (int)Controller::curr()->getRequest()->param('ID'); //grab ID from URL query string
        
        $this->keywords = Catalogue::get()->where(array('id ='.$this->id))->column($colName = "keywords"); //get keywords
        $this->keywordsArr = parent::__convertAndCleanList($this->keywords, ','); // creates array into pieces
        $this->video = Catalogue::get()->where(array('id ='.$this->id))->setQueriedColumns(array("Video_title", "trilogy")); //get title
        
    }
    /**
     * main call to build profile of title
     * 
     * @return array
     */
    public function profile()
    {
        $this->getIMDBMetadata();
        
        $sqlQuery = "SELECT catalogue.*, member.ID as MID, member.Email, member.FirstName, member.Surname 
                     FROM catalogue 
                     LEFT JOIN member ON catalogue.Owner = member.ID 
                     WHERE catalogue.ID = '{$this->id}'";

        $records = DB::query($sqlQuery);
        
        if ($records)
        {
            $set = new ArrayList();

            foreach ($records as $record)
            {
                $record['lastupdatedreadable'] = parent::humanTiming($record['LastEdited']);
                $record['seasonLinks'] = $this->seasonLinks($record['Seasons']);
                
                $set->push(new ArrayData($record));
            }
            
            return $set;
        }
        
    }

    /**
     * creates IMDB links for seasons
     * 
     * @param item <string>
     * @param imdbID <string>
     * 
     * @return string
     */
    public function createLinks (&$item, $imdbID)
    { 
        return $item = '<a href="http://www.imdb.com/title/'.$imdbID.'/episodes?season='.$item.'">'. $item. '</a>';
    }    
    
    /**
     * builds html for IMDB series links
     * 
     * @return array
     */
    public function seasonLinks($string)
    {
        if($string != null)
        {
            $imdb = $this->getIMDBMetadata();
            
            $pattern = '/[^\d|]/';
            $numbers = preg_replace($pattern,'', $string);

            $clean = explode("|",$numbers);

            $newArray = $clean;
            array_walk($newArray, array($this, 'createLinks'), $imdbID = $imdb[0]->imdbID);
            $result = implode(' | ', $newArray);
         
            return $result;
                
        }
    }
    
    /**
     * returns an array of titles related to the keyword of the viewed title
     * 
     * @return array
     */
    public function relatedTitles()
    {
       if($this->video[0]->trilogy == null)
       {
           return false;
           
       } else {
           
           return Catalogue::get()->where(array("trilogy='" . $this->video[0]->trilogy."'"))->exclude('ID', $this->id);
       }
       
    }

    /**
     * returns an array of results that contain titles based on keyword metadata
     * 
     * @return array
     */
    public function seeAlsoTitles()
    {
        //check how many keywords
            
            //if keywords <= 1 then check trilogy == keyword
            $trilogy = array($this->video[0]->trilogy);
            $trilogy = array_map('strtolower', $trilogy);
            $array = array_diff(array_map('strtolower', $this->keywordsArr), $trilogy);
            
            //loop over values so we can create WHERE like clauses
            $clauses = array();
            foreach ($array as $value)
            {
              $clauses[] = 'keywords LIKE \'%' . Convert::raw2sql($value) . '%\'';  
            }
            
            if($this->video[0]->trilogy == null)
                return Catalogue::get()->where(implode(' OR ', $clauses))->exclude('ID', $this->id);

            if(count($this->keywordsArr) <= 1)
            {
                return false;
                
            } else {
               //keywords are only 1, so we will return back array of keyword results.
               return Catalogue::get()->where(implode(' OR ', $clauses))->exclude('trilogy', $this->video[0]->trilogy); //get all titles related to wolverine and exclude itself from result.
            }
    }
    
    /**
     * gets metadata from OMDBAPI and saves it to local server
     * 
     * @return array
     */
    public function getIMDBMetadata()
    {
        $sqlQueryTitle = "SELECT Video_title, imdbID
                          FROM catalogue
                          WHERE catalogue.ID = '{$this->id}'";

        $recordsVideoTitle = DB::query($sqlQueryTitle);
        
        if ($recordsVideoTitle)
        {
            $set2 = new ArrayList();
            foreach ($recordsVideoTitle as $title)
            {
                $titleEncoded  = urlencode($title['Video_title']); //urlencoded fields only allowed to web api
                $sanitized = preg_replace('/[^a-zA-Z0-9-_\.]/','', $title['Video_title']); //sanitize for disallowed filename characters
                
                //check if metadata already exists on server
                if(!file_exists(JSONDIR."{$sanitized}.txt"))
                {
                    //no json file found, load from API
                    ($title['imdbID'] != null) ? $url = "http://www.omdbapi.com/?i=" .  $title['imdbID'] : $url = "http://www.omdbapi.com/?t=" .  $titleEncoded; //use imdb id if its there
                    
                    $json = file_get_contents($url);
                    $data = json_decode($json);
                    
                    file_put_contents(JSONDIR."{$sanitized}.txt", json_encode($data)); //save IMDB metadata local server
                    
                } else {
                    //json file found, load from server
                    $data = json_decode(file_get_contents(JSONDIR."{$sanitized}.txt")); //get JSON data from local server
                    
                    if($data->{'Response'} == "False")
                    {
                        $title['error'] = "video was not found in IMDB";
                        $title['errorType'] = "warning";
                        $set2->push(new ArrayData($title));
                        
                        return $set2;
                        
                    } else {
                            
                        if($data->{'Poster'} != "N/A") 
                        {
                            //if API returns a URI for poster, then save it
                            file_put_contents(POSTERSDIR."{$sanitized}.jpg", file_get_contents($data->{'Poster'})); //save poster to local server
                            
                        } else {
                            // API did not return a URI for poster, so use blank.png
                            $title['VideoPoster'] = "./assets/Uploads/blank.png";
                        }
                        
                        $title['errorType'] = "hide";
                        $title['VideoPoster'] = "./assets/Uploads/{$sanitized}.jpg";
                        $title['Year'] = $data->{'Year'};
                        $title['Director'] = $data->{'Director'};
                        $title['Actors'] = $data->{'Actors'};
                        $title['Plot'] = $data->{'Plot'};
                        $title['Runtime'] = $data->{'Runtime'};
                        $title['imdbID'] = $data->{'imdbID'};
                        
                        $set2->push(new ArrayData($title));
                    }
                }
            }
            return $set2;
        }  
    }
}
