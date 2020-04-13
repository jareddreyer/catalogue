<?php
class ProfilePage extends Page
{
    private static $allowed_children = array();
}

class ProfilePage_Controller extends Page_Controller
{
    public $keywordsArr, $video;

    private static $allowed_actions = array('profile');

    private static $url_handlers = array(
        'title/$ID' => 'profile'
    );

    public function init()
    {
        parent::init();

        $keywords = Catalogue::get()
                                ->where(array('id = '.$this->id))
                                ->column('Keywords');

        $this->keywordsArr = parent::convertAndCleanList($keywords, ','); // creates array into pieces
        $this->video = Catalogue::get()
                                    ->setQueriedColumns(array("VideoTitle", "trilogy"))
                                    ->byID($this->id); //get title
    }

    /**
     * main call to build profile of title
     *
     * @return ViewableData_Customised
     */
    public function profile()
    {

        $sqlQuery = "SELECT Catalogue.*, Member.ID as MID, Member.Email, Member.FirstName, Member.Surname 
                     FROM Catalogue 
                     LEFT JOIN Member ON Catalogue.Owner = Member.ID 
                     WHERE Catalogue.ID = '$this->id'";

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

                $set->push(ArrayData::create($record));
            }

            return $this->customise(['profile' => $set , 'getIMDBMetadata' => $metadata]);
        }

    }

    /**
     * builds html for IMDB series links
     *
     * @return string
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
     * @return mixed
     */
    public function relatedTitles()
    {
       if($this->video->trilogy !== null)
       {
           return Catalogue::get()
                                ->where(array("trilogy='" . $this->video[0]->trilogy."'"))
                                ->exclude('ID', $this->id)
                                ->sort('year');
       }
       return false;
    }

    /**
     * returns an array of results that contain titles based on keyword metadata
     *
     * @return mixed
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
     * @return ArrayList
     * @throws ValidationException
     */
    public function getIMDBMetadata()
    {
        //get video title and IMDBID values from Catalogue DB
        $imdbMetadata = Catalogue::get()->setQueriedColumns(array("VideoTitle", "IMDBID"))->byID($this->id);

        if ($imdbMetadata->exists())
        {
            //if title/ID exists
            $set2 = new ArrayList();

                $titleEncoded  = urlencode($imdbMetadata->VideoTitle); //urlencoded fields only allowed to web api
                $sanitized = preg_replace('/[^a-zA-Z0-9-_\.]/','', $imdbMetadata->VideoTitle); //sanitize for disallowed filename characters

                //check if metadata already exists on server
                if(!file_exists($this->jsonPath."{$sanitized}.txt"))
                {
                    //no json file found, load from API
                    ($imdbMetadata->IMDBID !== null) ? $url = "http://www.omdbapi.com/?apikey=".$this->apiKey."&i=" .  $imdbMetadata->imdbID : $url = "http://www.omdbapi.com/?apikey=".APIKEY."&t=" .  $titleEncoded; //use imdb id if its there

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
                        // create asset folder path
                        Folder::find_or_make($this->jsonAssetsFolderName);

        		        $rawJsonFilename = "{$sanitized}.txt";
                        $rawJsonPath = $this->jsonPath . $rawJsonFilename;

                        //save IMDB metadata local server
                        file_put_contents($rawJsonPath, json_encode($data));

                        // creating dataobject this needs refactoring in SS4 to use assetsFileStore class
                        $poster = File::create();
                        $poster->Title = $title;
                        $poster->Filename = $this->jsonAssetsFolderName . $rawJsonFilename;
                        $poster->write();

                        $data->{'VideoPoster'} = $this->checkPosterExists($data, $sanitized);

            			$set2->push(new ArrayData(
                                                    $this->jsonDataToArray($data, $sanitized)
                            )
                        );
        		    }

                } else {

                    //json file found, load from server
                    $data = json_decode(file_get_contents($this->jsonPath."{$sanitized}.txt")); //get JSON data from local server

                    if($data->{'Poster'} != "N/A")
                    {
                        $data->{'VideoPoster'} = $this->checkPosterExists($data, $sanitized);
                    } else {

                        // API did not return a URI for poster, so use blank.png
                        return 'blank.png';
                    }

                    $set2->push(new ArrayData($this->jsonDataToArray($data, $sanitized)));
                }

            return $set2;
        }
    }

    public function jsonDataToArray ($data, $sanitized, $title = null)
    {
        $title['errorType'] = "hide";
        $title['VideoPoster'] = ASSETS_DIR . $this->postersAssetsFolderName."{$sanitized}.jpg";
        $title['Year'] = $data->{'Year'};
        $title['Director'] = $data->{'Director'};
        $title['Actors'] = $data->{'Actors'};
        $title['Plot'] = $data->{'Plot'};
        $title['Runtime'] = $data->{'Runtime'};
        $title['IMDBID'] = $data->{'imdbID'};

        return $title;
    }

    /**
     * checks if we have a poster saved to assets already.
     * @param $data
     * @param $filename
     * @return string
     * @throws ValidationException
     */
    public function checkPosterExists ($data, $filename)
    {
        // setup raw filename and path
        $rawPosterFilename = "{$filename}.jpg";
        $posterID = Catalogue::get()->byID($this->ID)->Poster;

        if(!Image::get()->byID($posterID)) {
            // create asset folder path
            Folder::find_or_make($this->postersAssetsFolderName);

            // whole web path to posters
            $rawPosterPath = $this->postersPath . $rawPosterFilename;

            try {
                file_put_contents($rawPosterPath, file_get_contents($data->{'Poster'}));
            } catch (Exception $exception) {
                user_error('we had trouble saving posters to ' . $rawPosterPath );
            }

            // creating dataobject this needs refactoring in SS4 to use assetsFileStore class
            $poster = Image::create();
            $poster->Title = $filename;
            $poster->Filename = ASSETS_DIR . $this->postersAssetsFolderName . $rawPosterFilename;
            $poster->write();

            return ASSETS_DIR . $this->postersAssetsFolderName . $rawPosterFilename;
        }

    }

}
