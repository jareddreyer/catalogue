<?php
class ProfilePage extends Page
{
    private static $allowed_children = array();
}

class ProfilePage_Controller extends Page_Controller
{
    public $id, $keywordsArr, $video;

    private static $allowed_actions = array('profile');

    private static $url_handlers = array(
        'title/$ID' => 'profile'
    );

    public function init()
    {
        parent::init();
        
        (int) $this->id = Controller::curr()->getRequest()->param('ID'); //grab ID from URL query string
        
         $keywords = Catalogue::get()
                                ->where(array('id = '.$this->id))
                                ->column('Keywords');
                                
        $this->keywordsArr = parent::convertAndCleanList($keywords, ','); // creates array into pieces
        $this->video = Catalogue::get()
                                    ->setQueriedColumns(array("Video_title", "trilogy"))
                                    ->byID($this->id); //get title
        
    }

    /**
     * main call to build profile of title
     * 
     * @return array
     */
    public function profile()
    {        
        
        $sqlQuery = "SELECT Catalogue.*, Member.ID as MID, Member.Email, Member.FirstName, Member.Surname 
                     FROM Catalogue 
                     LEFT JOIN Member ON Catalogue.Owner = Member.ID 
                     WHERE Catalogue.ID = '{$this->id}'";

        $records = DB::query($sqlQuery);

        $metadata = $this->getIMDBMetadata();

        if ($records)
        {
            $set = ArrayList::create();

            foreach ($records as $record)
            {
                $record['lastupdatedreadable'] = parent::humanTiming($record['LastEdited']);
                $record['seasonLinks'] = $this->seasonLinks($record['Seasons']);
                $record['displayComments'] = parent::displayComments($record['Comments']);
                $record['path'] = POSTERSWEBPATH;

                $set->push(ArrayData::create($record));
            }
            
            return $this->customise(array('profile' => $set, 'getIMDBMetadata'=>$metadata));
        }
        
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
            $imdb = $this->getIMDBMetadata(); //get all metadata for title
            
            $pattern = '/[^\d|]/'; 
            $numbers = preg_replace($pattern,'', $string);
            $arraySeasons = explode("|",$numbers); //explode the season string into array of season numbers.            
            $imdbID = $imdb[0]->imdbID; //assign variable for use in annoymous function.

            $seasonLinksArray = array_map(function ($v) use ($imdbID) { return '<a href="http://www.imdb.com/title/'.$imdbID.'/episodes?season='.$v.'">'. $v. '</a>';}, $arraySeasons); //apply callback annoymous function over the array elements, adding anchor links
            $result = implode(' | ', $seasonLinksArray); //implode back to array of 1 string.

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

       if($this->video->trilogy == null)
       {
           return false;
           
       } else {
           
           return Catalogue::get()
                                ->where(array("trilogy='" . $this->video[0]->trilogy."'"))
                                ->exclude('ID', $this->id)
                                ->sort('year');
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
            $trilogy = array($this->video->trilogy);
            $trilogy = array_map('strtolower', $trilogy);
            $array = array_diff(array_map('strtolower', $this->keywordsArr), $trilogy);
            
            //loop over values so we can create WHERE like clauses
            $clauses = array();
            foreach ($array as $value)
            {
              $clauses[] = 'keywords LIKE \'%' . Convert::raw2sql($value) . '%\'';  
            }
            
            if($this->video->trilogy == null && count($this->keywordsArr) > 1)
                return Catalogue::get()->where(implode(' OR ', $clauses))->exclude('ID', $this->id);

            if(count($this->keywordsArr) <= 1)
            {
                return false; //nothing to return so return a false so view doesn't display anything.
                
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
        //get video title and IMDBID values from Catalogue DB
        $imdbMetadata = Catalogue::get()->setQueriedColumns(array("Video_title", "imdbID"))->byID($this->id);        

        if ($imdbMetadata->exists())
        {
            //if title/ID exists            
            $set2 = new ArrayList();
            
                $titleEncoded  = urlencode($imdbMetadata->Video_title); //urlencoded fields only allowed to web api
                $sanitized = preg_replace('/[^a-zA-Z0-9-_\.]/','', $imdbMetadata->Video_title); //sanitize for disallowed filename characters
                
                //check if metadata already exists on server
                if(!file_exists(JSONDIR."{$sanitized}.txt"))
                {

                    //no json file found, load from API
                    ($imdbMetadata->imdbID != null) ? $url = "http://www.omdbapi.com/?apikey=".APIKEY."&i=" .  $imdbMetadata->imdbID : $url = "http://www.omdbapi.com/?apikey=".APIKEY."&t=" .  $titleEncoded; //use imdb id if its there
                    
                    //now create json file of api data
        		    $json = file_get_contents($url);
                    $data = json_decode($json);
                    $title = [];

        		    if($data->{'Response'} == "False")
        		    {
                        switch ($data->{'Error'}) {
                            case 'Incorrect IMDb ID.':
                                $title['error'] = "IMDB ID does not exist, you must have entered it directly to the database";
                                $title['errorType'] = "danger";
                            break;

                            case 'Invalid API key!':
                                $title['error'] = "Could not connect to omdbapi.com api, requires authorization key.";
                                $title['errorType'] = "danger";
                            break;

                            case 'Movie not found!':
                                $title['error'] = "Could not find this title, you must have entered incorrect to database.";
                                $title['errorType'] = "danger";
                        }            			

                        $set2->push(new ArrayData($title));

        		    } else {                        
                        
                        file_put_contents(JSONDIR."{$sanitized}.txt", json_encode($data)); //save IMDB metadata local server                        
                        $data->{'VideoPoster'} = $this->checkPosterExists($data, $sanitized);

            			$set2->push(new ArrayData( 
                                                    $this->jsonDataToArray($data, $sanitized)
                            )
                        );
        		    }
                    
                } else {

                    //json file found, load from server
                    $data = json_decode(file_get_contents(JSONDIR."{$sanitized}.txt")); //get JSON data from local server
                    
                    $data->{'VideoPoster'} = $this->checkPosterExists($data, $sanitized);

                    $set2->push(new ArrayData($this->jsonDataToArray($data, $sanitized)));
                    
                }

            return $set2;
        }  
    }

    public function jsonDataToArray ($data, $sanitized, $title = null) 
    {
        $title['errorType'] = "hide";
        $title['VideoPoster'] = POSTERSDIR."/{$sanitized}.jpg";
        $title['Year'] = $data->{'Year'};
        $title['Director'] = $data->{'Director'};
        $title['Actors'] = $data->{'Actors'};
        $title['Plot'] = $data->{'Plot'};
        $title['Runtime'] = $data->{'Runtime'};
        $title['imdbID'] = $data->{'imdbID'};

        return $title;
    }

    public function checkPosterExists ($data, $sanitized) 
    {
        if($data->{'Poster'} != "N/A") 
        {                        
            //if API returns a URI for poster, then save it
            file_put_contents(POSTERSDIR."{$sanitized}.jpg", file_get_contents($data->{'Poster'})); //save poster to local server
            
        } else {
            
            // API did not return a URI for poster, so use blank.png
            return $title['VideoPoster'] = "./assets/Uploads/blank.png";
        }        
    }

}
