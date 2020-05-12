<?php
class ProfilePage extends Page
{
    private static $allowed_children = array();
}

class ProfilePage_Controller extends Page_Controller
{

    private static $allowed_actions = ['profile'];

    private static $url_handlers = [
        'title/$ID' => 'profile'
    ];

    /**
     * main call to build profile of title
     *
     * @return ViewableData_Customised
     * @throws ValidationException
     */
    public function profile()
    {
        // get metadata for downloading and displaying
        $this->getMetadata();

        // get db record
        $title = Catalogue::get()->byID($this->slug);

        if ($title)
        {
            foreach ($title as $record)
            {
                $record->genres = $this->getCleanGenresList($record->Genre);
                $record->displayComments = parent::displayComments($record->Comments);
            }

            return $this->customise(
                [
                    'profile' => $record,
                ]
            );
        }

    }

    /**
     * builds html for IMDB series links
     *
     * @return string
     * @throws ValidationException
     */
    public function seasonLinks()
    {
        $title = Catalogue::get()->byID($this->slug);

        if($title->Seasons != null)
        {

            // remove season word so we get a list of just numbers csv
            $seasons = str_replace('Season ', '', $title->Seasons);
            $arraySeasons = explode(',', $seasons);

            $seasonLinksArray = array_map(function(&$arraySeasons) use ($title) { return '<a href="http://www.imdb.com/title/'.$title->IMDBID.'/episodes?season='.$arraySeasons.'">'. $arraySeasons. '</a>'; }, $arraySeasons );

            //implode back to array of 1 string.
            return implode(' | ', $seasonLinksArray);
        }

        return;
    }

    /**
     * returns an array of titles related to the keyword of the viewed title
     *
     * @return mixed
     */
    public function relatedTitles()
    {
        //get title
        $video = Catalogue::get()->byID($this->slug);

        if($video->Trilogy !== null)
        {
           return Catalogue::get()
                                ->filter(['Trilogy' => $video->Trilogy])
                                ->exclude('ID', $this->slug)
                                ->sort('Year');
        }

        return false;
    }

    /**
     * returns an array of results that contain titles based on keyword metadata
     * Excludes the Trilogy from the array set seeing as that will be included in the
     * relatedTitles(), so does not need to be included twice.
     *
     * @todo needs refactoring, too heavy on the if statements
     *
     * @see \ProfilePage_Controller::relatedTitles()
     * @see \Page_Controller::convertAndCleanList()
     *
     * @return mixed
     */
    public function seeAlsoTitles()
    {
        // First set the lazy loading.
        $video = Catalogue::get();
        $keywords = $video->byID($this->slug);

        // check keywords exist and continue
        if($keywords->Keywords) {
            // create a clean array for us
            $keywordsArr = parent::convertAndCleanList($keywords->Keywords, ',');
            $keywordsArrCount = count($keywordsArr);

            // we have more than one keyword
            if($keywordsArrCount >= 1  ) {

                // grab us all the keywords that does not = Trilogy
                if($trilogy = [$keywords->Trilogy]) {

                    $includeTitles = array_diff($keywordsArr, $trilogy);
                    $trilogyClause = '("Trilogy" <> \''. $keywords->Trilogy .'\' OR "Trilogy" IS NULL) AND (';
                }

                //loop over values so we can create WHERE like clauses
                $includeClauses = [];
                foreach ($includeTitles as $value) {
                    $includeClauses[] = 'Keywords LIKE \'%' . Convert::raw2sql($value) . '%\'';
                }

                // close off the sql properly
                if($trilogy) {
                    $trilogyClause .= implode(' OR ', $includeClauses) . ')';
                }  else {
                    $trilogyClause .= implode(' OR ', $includeClauses);
                }

                return $video
                    ->where($trilogyClause)
                    ->exclude('ID', $this->slug);

            } else {
                // we only have one keyword
                return $video
                    ->where('"Keywords = \'' . $keywords->Keywords . '\'')
                    ->exclude('ID', $this->slug);
            }
        }

        return;
    }

    /**
     * saves and gets metadata from OMDBAPI.
     * This function will also create relationships and save a local file.
     *
     * @return ArrayList
     * @throws ValidationException
     */
    public function getMetadata()
    {
        //get video title and IMDBID values from Catalogue DB
        $imdbMetadata = Catalogue::get()->setQueriedColumns(["Title" , "IMDBID", 'MetadataID'])->byID($this->slug);

        if ($imdbMetadata !== null) {
            //sanitize for disallowed filename characters
            $jsonFilename = $this->cleanFilename($imdbMetadata->Title, $imdbMetadata->IMDBID, 'txt');

            //if title/ID exists
            $result = ArrayList::create();

            //check if metadata file already exists on server
            if (!file_exists($this->jsonPath . $jsonFilename)) {

                //urlencoded fields only allowed to web api
                $titleEncoded = urlencode($imdbMetadata->Title);

                //Construct the API call
                $url = "http://www.omdbapi.com/?apikey=" . $this->apiKey;

                if ($imdbMetadata->IMDBID == null) {
                    $url .= '&t=' . $titleEncoded . '&type='.$imdbMetadata->Type;
                } else {
                    $url .= '&i=' . $imdbMetadata->IMDBID;
                }

                if ($imdbMetadata->Year !== null && $imdbMetadata->IMDBID == null) {
                    $url .= '&y=' . $imdbMetadata->Year;
                }

                //now create json file of api data
                try {
                    $json = file_get_contents($url);
                } catch (Exception $e) {
                    user_error('There was an issue connecting to the omdb API: ' . $e);
                }

                $data = json_decode($json);
                $title = [];

                if ($data->{'Response'} == "False") {
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

                    $result->push(ArrayData::create($title));

                } else {

                    // create asset folder path
                    $parentID = Folder::find_or_make($this->jsonAssetsFolderName);

                    // override jsonfilename because we dont have ImdbID in the database
                    $jsonFilename = $this->cleanFilename($imdbMetadata->Title, $data->{'imdbID'}, 'txt');

                    // set entire path to file
                    $rawJsonPath = $this->jsonPath . $jsonFilename;

                    //save IMDB metadata local server
                    try {
                        file_put_contents($rawJsonPath, json_encode($data));
                    } catch (Exception $exception) {
                        user_error('we had trouble saving poster metadata to ' . $rawJsonPath);
                    }

                    // creating dataobject this needs refactoring in SS4 to use assetsFileStore class
                    $metadata = File::create();
                    $metadata
                        ->update(
                            [
                                'Title'     => $imdbMetadata->Title . ' (' . $data->{'Year'} . ')',
                                'ParentID'  => $parentID->ID,
                                'Filename ' => ASSETS_DIR . $this->jsonAssetsFolderName . $jsonFilename,
                            ]
                        )
                        ->write();

                    // update the relation
                    $updateCatalog = Catalogue::create();
                    $updateCatalog
                        ->update(
                            [
                                'ID'         => $imdbMetadata->ID,
                                'IMDBID'     => $data->{'imdbID'},
                                'Year'       => $data->{'Year'},
                                'Genre'      => $data->{'Genre'},
                                'MetadataID' => $metadata->ID,
                            ]
                        )
                        ->write();

                    if ($data->{'Poster'} != "N/A") {
                        $data->{'Poster'} = $this->checkPosterExists($data, $this->cleanFilename($imdbMetadata->Title, $data->{'imdbID'}, 'image'));
                    }

                    $result->push(
                        ArrayData::create($this->jsonDataToArray($data))
                    );
                }

            } else {

                //json file found, load from server
                try {
                    //get JSON data from local server
                    $data = json_decode(file_get_contents($this->jsonPath . $jsonFilename));

                    // metadata exists but poster may not, so lets save and get it and add it to imdb scope
                    if ($data->{'Poster'} != "N/A") {
                        $data->{'Poster'} = $this->checkPosterExists($data, $this->cleanFilename($imdbMetadata->Title, $data->{'imdbID'}, 'image'));
                    }

                    $result->push(ArrayData::create($this->jsonDataToArray($data)));
                } catch (Exception $exception) {
                    user_error('we had trouble saving posters to ' . $this->jsonPath);
                }
            }
        }

        return $result;
    }

    /**
     * resets the IMDBAPI result to arraylist for the template.
     * L = DBFieldname, R = APIFieldname
     *
     * @todo refactor into a foreach loop so don't have specify all field names
     *
     * @param $data
     * @return mixed
     */
    public function jsonDataToArray ($data)
    {
        $title['Year'] = $data->{'Year'};
        $title['Poster'] = $data->{'Poster'};
        $title['Director'] = $data->{'Director'};
        $title['Actors'] = $data->{'Actors'};
        $title['Plot'] = $data->{'Plot'};
        $title['Runtime'] = $data->{'Runtime'};
        $title['IMDBID'] = $data->{'imdbID'};
        $title['Rated'] = $data->{'Rated'};
        $rating = explode('/', current($data->{'Ratings'})->{'Value'});
        // first rating (if available) is always the IMDB.com ratings, otherwise this will take whatever rating is available.
        $title['Rating'] = $rating[0];

        return $title;
    }

    /**
     * Helper function to tidy up the csv value of genres.
     * @todo this is legacy to help with incorrect tagIt jquery plugin setting for whitespaces after commas
     *
     * @param $genres
     * @return mixed
     */
    public function getCleanGenresList ($genres)
    {
        return str_replace(',', ', ', $genres);
    }
}
