<?php
class ProfilePage extends Page
{
    private static $allowed_children = array();
}

class ProfilePage_Controller extends Page_Controller
{
    public $keywordsArr, $video;

    private static $allowed_actions = ['profile'];

    private static $url_handlers = [
        'title/$ID' => 'profile'
    ];

    public function init()
    {
        parent::init();

        $keywords = Catalogue::get()
                                ->where(array('id = '.$this->slug))
                                ->column('Keywords');

        $this->keywordsArr = parent::convertAndCleanList($keywords, ','); // creates array into pieces
        $this->video = Catalogue::get()
                                    ->setQueriedColumns(["VideoTitle" , "Trilogy"])
                                    ->byID($this->slug); //get title
    }

    /**
     * main call to build profile of title
     *
     * @return ViewableData_Customised
     * @throws ValidationException
     */
    public function profile()
    {
        $sqlQuery = "SELECT Catalogue.*, Member.ID as MID, Member.Email, Member.FirstName, Member.Surname 
                     FROM Catalogue 
                     LEFT JOIN Member ON Catalogue.OwnerID = Member.ID 
                     WHERE Catalogue.ID = '$this->slug'";

        $records = DB::query($sqlQuery);

        $metadata = $this->getIMDBMetadata();

        if ($records)
        {
            $set = ArrayList::create();

            foreach ($records as $record)
            {
                $record['lastEditedAgo'] = parent::humanTiming($record['LastEdited']); // @todo refactor to remove this
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
     * @throws ValidationException
     */
    public function seasonLinks($string)
    {
        if($string != null)
        {
            $imdb = $this->getIMDBMetadata(); //get all metadata for title

            $pattern = '/[^\d|]/';
            $numbers = preg_replace($pattern,'', $string);
            $arraySeasons = explode("|",$numbers); //explode the season string into array of season numbers.
            $imdbID = $imdb->imdbID; //assign variable for use in annoymous function.

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
       if($this->video->Trilogy !== null)
       {
           return Catalogue::get()
                                ->where(["Trilogy='" . $this->video->Trilogy."'"])
                                ->exclude('ID', $this->slug)
                                ->sort('Year');
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
        $trilogy = [$this->video->Trilogy];
        $trilogy = array_map('strtolower', $trilogy);
        $array = array_diff(array_map('strtolower', $this->keywordsArr), $trilogy);

        //loop over values so we can create WHERE like clauses
        $clauses = [];
        foreach ($array as $value)
        {
          $clauses[] = 'Keywords LIKE \'%' . Convert::raw2sql($value) . '%\'';
        }

        if($this->video->Trilogy == null && count($this->keywordsArr) > 1)
            return Catalogue::get()->where(implode(' OR ', $clauses))->exclude('ID', $this->slug);

        if(count($this->keywordsArr) <= 1)
        {
            return false; //nothing to return so return a false so view doesn't display anything.

        } else {
           //keywords are only 1, so we will return back array of keyword results.
            //get all titles related to itself (e.g. 'wolverine') and exclude itself from result.

           return Catalogue::get()
               ->where(implode(' OR ', $clauses))
               ->exclude('Trilogy', $this->video->Trilogy);
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
        $imdbMetadata = Catalogue::get()->setQueriedColumns(["VideoTitle" , "IMDBID"])->byID($this->slug);

        //sanitize for disallowed filename characters
        $jsonFilename = $this->cleanFilename($imdbMetadata->VideoTitle, $imdbMetadata->IMDBID, 'txt');
        $posterFilename = $this->cleanFilename($imdbMetadata->VideoTitle, $imdbMetadata->IMDBID, 'image');

        if ($imdbMetadata->exists())
        {
            //if title/ID exists
            $result = ArrayList::create();

            $titleEncoded  = urlencode($imdbMetadata->VideoTitle); //urlencoded fields only allowed to web api

            //check if metadata already exists on server
            if(!file_exists($this->jsonPath. $jsonFilename))
            {
                //no json file found, load from API
                if($imdbMetadata->IMDBID !== null) {
                    //use imdb id if its there
                    $url = "http://www.omdbapi.com/?apikey=".$this->apiKey."&i=" . $imdbMetadata->IMDBID;
                } else {
                    $url = "http://www.omdbapi.com/?apikey=".$this->apiKey."&t=" . $titleEncoded;
                }

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

                    $result->push( ArrayData::create($title));

                } else {

                    // create asset folder path
                    $parentID = Folder::find_or_make($this->jsonAssetsFolderName);

                    // set entire path to file
                    $rawJsonPath = $this->jsonPath . $jsonFilename;

                    //save IMDB metadata local server
                    try {
                        file_put_contents($rawJsonPath, json_encode($data));
                    } catch (Exception $exception) {
                        user_error('we had trouble saving poster metadata to ' . $rawJsonPath );
                    }

                    // creating dataobject this needs refactoring in SS4 to use assetsFileStore class
                    $poster = File::create();
                    $poster->Title = $imdbMetadata->VideoTitle;
                    $poster->ParentID = $parentID->ID;
                    $poster->Filename = ASSETS_DIR . $this->jsonAssetsFolderName . $jsonFilename;
                    $poster->write();

                    $data->{'VideoPoster'} = $this->checkPosterExists($data, $posterFilename);

                    $result->push(
                            ArrayData::create($this->jsonDataToArray($data)
                        )
                    );
                }

            } else {

                //json file found, load from server
                try {
                    //get JSON data from local server
                    $data = json_decode(file_get_contents($this->jsonPath.$jsonFilename));

                    if($data->{'Poster'} != "N/A")
                    {
                        $data->{'VideoPoster'} = $this->checkPosterExists($data, $posterFilename);
                    } else {
                        // API did not return a URI for poster, so use blank.png
                        $data->{'VideoPoster'} = THEMES_PATH. '/simple/images/blank.png';
                    }

                    $result->push(ArrayData::create($this->jsonDataToArray($data)));

                    return $result;

                } catch (Exception $exception) {
                    user_error('we had trouble saving posters to ' . $this->jsonPath );
                }
            }
        }
    }

    /**
     * resets the IMDBAPI result to arraylist for the template.
     * L = DBFieldname, R = APIFieldname
     *
     * @param $data
     * @return mixed
     */
    public function jsonDataToArray ($data)
    {
        $title['errorType'] = "hide";
        $title['VideoPoster'] = $data->{'VideoPoster'};
        $title['Year'] = $data->{'Year'};
        $title['Director'] = $data->{'Director'};
        $title['Actors'] = $data->{'Actors'};
        $title['Plot'] = $data->{'Plot'};
        $title['Runtime'] = $data->{'Runtime'};
        $title['IMDBID'] = $data->{'imdbID'};

        return $title;
    }
}
