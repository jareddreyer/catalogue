<?php

namespace App\Catalogue\PageTypes;

use App\Catalogue\Models\Catalogue;
use PageController;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Folder;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Dev\Debug;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\ValidationException;
use SilverStripe\View\ArrayData;
use SilverStripe\View\ViewableData_Customised;
use stdClass;
use Throwable;

class ProfilePageController extends PageController
{

    private static array $allowed_actions = ['profile'];

    private static array $url_handlers = [
        'title/$ID' => 'profile',
    ];

    /**
     * @todo move to service
     * @var string[]
     */
    private static array $api_base_uri = [
      'tmdb' => 'https://api.themoviedb.org/3/',
      'omdb' => 'https://www.omdbapi.com/',
    ];

    /**
     * main call to build profile of title
     * @todo tidy up how profile => $record is set
     * @todo needs tidying up for early exists
     */
    public function profile(): DBHTMLText|ViewableData_Customised|null
    {
        // get db record
        $title = Catalogue::get()->byID($this->slug);

        if (!$title) {
            return null;
        }

        foreach ($title as $record) {
            $record->genres = $this->getFieldFiltersList($record->Genre, 'badge filters');
            $record->keywords = $this->getFieldFiltersList($record->Keywords, 'badge filters');
        }

        $data = [
            'profile' => $record,
            'trailers' => $this->getTrailers(),
        ];

        if ($this->request->isAjax()) {
            return $this->customise($data)->renderWith(['Includes/ProfileAjax']);
        }

        return $this->customise($data);
    }

    /**
     * Builds html for IMDB series links
     */
    public function seasonLinks(): ArrayList|null
    {
        $title = Catalogue::get()->byID($this->slug);

        if ($title->Seasons === null || $title->Type === 'movie') {
            return null;
        }

        $seassonsArrayList = ArrayList::create();

        if ($title->Seasons !== null) {
            // remove season word so we get a list of just numbers csv
            $seasons = str_replace('Season ', '', $title->Seasons);
            $arraySeasons = explode(',', $seasons);

            foreach ($arraySeasons as $season) {
                $link = '<a href="http://www.imdb.com/title/' . $title->IMDBID .
                    '/episodes?season=' . $season . '">' . $season .
                    '</a>';
                $seassonsArrayList->push(ArrayData::create(['seasons' => $link]));
            }
        }

        return $seassonsArrayList;
    }

    /**
     * returns an array of titles related to the keyword of the viewed title
     */
    public function relatedTitles(): DataList|bool
    {
        //get title
        $video = Catalogue::get()->byID($this->slug);

        if ($video->Collection !== null) {
            return Catalogue::get()
               ->filter(['Collection' => $video->Collection])
               ->exclude('ID', $this->slug)
               ->sort('Year');
        }

        return false;
    }

    /**
     * returns an array of results that contain titles based on keyword metadata
     * Excludes the Collection from the array set seeing as that will be included in the
     * relatedTitles(), so does not need to be included twice.
     *
     * @todo needs refactoring, too heavy on the if statements
     * @see \ProfilePage_Controller::relatedTitles()
     * @see \Page_Controller::convertAndCleanList()
     * @return mixed
     */
    public function seeAlsoTitles(): ?DataList
    {
        // First set the lazy loading.
        $catalogueItem = Catalogue::get_by_id($this->slug);

        // check keywords exist and continue
        if ($catalogueItem->Keywords === null) {
            return null;
        }

        // create a clean array for us
        $keywordsArr = $this->convertAndCleanList($catalogueItem->Keywords, ',');
        $keywordsArrCount = count($keywordsArr);

        // we have more than one keyword
        if ($keywordsArrCount >= 1 && $catalogueItem->Collection !== null) {
            $collection = [$catalogueItem->Collection];

            // grab us all the keywords that does not = collection
            $includeTitles = array_diff($keywordsArr, $collection);

            return Catalogue::get()->filterAny([
                'Keywords:ExactMatch' => $includeTitles ,
            ])->exclude('ID', $this->slug);
        }

        return Catalogue::get()->filterAny([
            'Keywords:ExactMatch' => $keywordsArr,
        ])->exclude('ID', $this->slug);
    }

    /**
     * saves and gets metadata from OMDBAPI.
     * This function will also create relationships and save a local file.
     *
     * @return ArrayList
     * @throws ValidationException
     * @throws HTTPResponse_Exception
     */
    public function getMetadata(): ArrayList|stdClass|null
    {
        // Get the video title from the Catalogue model.
        $catalogueItem = Catalogue::get_by_id($this->slug);

        // Early exit if this ID does not exist
        if (!$catalogueItem) {
            return null;
        }

        $result = ArrayList::create();

        // We have a catalogue item and now check we have a metadata file.
        if ($this->checkMetadataExists()) {
            // Get JSON data from local server
            $record = File::get_by_id($catalogueItem->MetadataID);
            $data = json_decode($record->getString());

            // Returning to the view so ArrayList everything
            $metadataArrayList = ArrayList::create();
            $metadataArrayList->push(ArrayData::create($this->jsonDataToArray($data)));

            // Build a HTTPRequest for fetching poster imagery.
            $posterRequestData = [
                'poster' => $data->Poster,
                'title' => $data->Title,
                'year'=> $data->Year,
                'IMDBID' => $data->imdbID,
            ];
            $request = $this->buildPosterRequest($posterRequestData);
            /**
             * We do not need to return here because this method only redundantly
             * fetches the poster if it does not exist.
             * Thus, we just fetch the poster.
             * @see PageController::getPosterImageByID()
             */
            $this->fetchPosterImage($request);

            return $metadataArrayList;
        }

        // We have no metadata file proceed to fetching.
        $metadataFilePath = Controller::join_links($this->postersFolderName, $this->metadataFolderName);

        // {@see urlencoded} fields only allowed to web api
        $titleEncoded = urlencode($catalogueItem->Title);

        // Construct the API call
        // @todo make api calls static parameters
        $url = self::$api_base_uri['omdb'] . '?apikey=' . self::$OMDBAPIKey;

        // @todo when moving to service we can use parameters to tidy these better
        if ($catalogueItem->IMDBID === null) {
            $url .= '&t=' . $titleEncoded . '&type=' . $catalogueItem->Type . '&plot=full';

            if ($catalogueItem->Year !== null) {
                $url .= '&y=' . $catalogueItem->Year;
            }
        } else {
            $url .= '&i=' . $catalogueItem->IMDBID . '&plot=full';
        }

        //now create json file of api data
        try {
            $json = file_get_contents($url);
        } catch (Throwable $e) {
            user_error('There was an issue connecting to the omdb API: ' . $e);
        }

        $data = json_decode($json);

        // Check if the API returned us a correct response.
        if ($data->{'Response'} === 'False') {

            $title = match ($data->Error) {
                'Incorrect IMDb ID.' => [
                    'error' => 'IMDB ID does not exist, you must have entered it directly to the database',
                    'errorType' => 'danger',
                ],
                'Invalid API key!' => [
                    'error' => 'Could not connect to omdbapi.com api, requires authorization key.',
                    'errorType' => 'danger',
                ],
                'Movie not found!' => [
                    'error' => 'Could not find this title, you must have entered it incorrectly into the database.',
                    'errorType' => 'danger',
                ],
                default => [
                    'error' => 'Something went wrong.',
                    'errorType' => 'danger',
                ],
            };

            // @todo return http error?
            $result->push(ArrayData::create($title));

        }
        else {
            // create asset folder path
            $parentID = Folder::find_or_make($metadataFilePath);

            // Sanitize for disallowed filename characters
            $cleanMetadataFilename = $this->cleanFilename($catalogueItem->Title, $data->imdbID, 'txt');
            $metadataFileName = Controller::join_links($metadataFilePath, $cleanMetadataFilename);

            // save IMDB metadata local server
            // creating dataobject this needs refactoring in SS4 to use assetsFileStore class
            $metadata = File::create();
            $metadata->setFromString(json_encode($data), $metadataFileName);
            $metadata->update([
                'Name' => $metadataFileName,
                'Title' => $catalogueItem->Title . ' (' . $data->{'Year'} . ')',
                'ParentID' => $parentID->ID,
            ]);
            $metadata->publishSingle();

            // update the relation
            $updateCatalog = Catalogue::get()->byID($this->slug);
            $updateCatalog->update([
                'ID' => $catalogueItem->ID,
                'IMDBID' => $data->{'imdbID'},
                'Year' => $data->{'Year'},
                'Genre' => $data->{'Genre'},
                'MetadataID' => $metadata->ID,
            ])->write();

            // Build a HTTPRequest for fetching poster imagery.
            if ($data->{'Poster'} !== 'N/A') {
                $posterRequestData = [
                    'poster' => $data->Poster,
                    'title' => $data->Title,
                    'year'=> $data->Year,
                    'IMDBID' => $data->imdbID,
                ];
                $request = $this->buildPosterRequest($posterRequestData);
                /**
                 * We do not need to return here because this method only redundantly
                 * fetches the poster if it does not exist.
                 * Thus, we just fetch the poster.
                 * @see PageController::getPosterImageByID()
                 */
                $this->fetchPosterImage($request);
            }

            $result->push(ArrayData::create($this->jsonDataToArray($data)));
        }

        return $result;
    }

    public function checkMetadataExists(): bool
    {
        $catalogueItem = Catalogue::get()->setQueriedColumns(['MetadataID'])->byID($this->slug);

        return !($catalogueItem->MetadataID === 0);
    }

    /**
     * We need to return the IMDBAPI result as an arraylist for the template.
     *
     * @todo refactor into a foreach loop so don't have specify all field names
     * @param string[] $data - api result
     */
    public function jsonDataToArray(stdClass $data): ?array
    {
        if ($data === []) {
            return [];
        }

        $title = [
            'Title' => $data->Title,
            'Year' => $data->Year,
            'Rated' => $data->Rated,
            'Released' => $data->Released,
            'Runtime' => $data->Runtime,
            'Genre' => $data->Genre,
            'Director' => $data->Director,
            'Writer' => $data->Writer,
            'Actors' => $data->Actors,
            'Plot' => $data->Plot,
            'Language' => $data->Language,
            'Country' => $data->Country,
            'Awards' => $data->Awards,
            'Poster' => $data->Poster,
            'Ratings' => $data->Ratings,
            'Metascore' => $data->Metascore,
            'imdbRating' => $data->imdbRating,
            'imdbVotes' => $data->imdbVotes,
            'imdbID' => $data->imdbID,
        ];

        $rating = explode('/', current($data->{'Ratings'})->{'Value'});
        // first rating (if available) is always the IMDB.com ratings, otherwise this will take whatever rating is available.
        $title['Rating'] = $rating[0];

        return $title;
    }

    /**
     * Helper function to tidy up the csv value of genres.
     *
     * @todo this is legacy to help with incorrect tagIt jquery plugin setting for whitespaces after commas
     * @deprecated no longer required as tagIT should always use a pipe with no spaces. *
     * @param $genres
     * @return mixed
     */
    public function getCleanGenresList($genres)
    {
        return str_replace(',', ', ', $genres);
    }

    /**
     * returns data for trailers from themoviedb.org
     * @todo needs a service and tidying up
     */
    public function getTrailers(): ArrayList|null
    {
        //get video title and IMDBID values from Catalogue DB
        $imdbMetadata = Catalogue::get_by_id($this->slug);

        if ($imdbMetadata->IMDBID === null) {
            return null;
        }

        //set type because tmdb uses 'tv' instead of 'series'
        $type = $imdbMetadata->Type === 'series' ? 'tv' : $imdbMetadata->Type;

        $tmdbAPIKey = 'api_key=' . self::$TMDBAPIKey;

        // get ID from tmddb.org
        try {
            $apiURL = self::$api_base_uri['tmdb'] . 'find/' .$imdbMetadata->IMDBID .
                '?' . $tmdbAPIKey . '&external_source=imdb_id';

            $json = file_get_contents($apiURL);
            $data = json_decode($json);
        } catch (Throwable $e) {
            user_error('There was an issue connecting to the omdb API: ' . $e);
        }

        // now get trailers from id
        try {
            $id = $data->{$type . '_results'}[0]->{'id'};

            $apiURL = self::$api_base_uri['tmdb'] . $type . '/' .$id . '/videos?' . $tmdbAPIKey;
            $json = file_get_contents($apiURL);
            $data = json_decode($json);

            $trailerKeysArray = array_keys(array_column($data->{'results'}, 'type'), 'Trailer');
            $trailersArray = ArrayList::create();

            foreach ($trailerKeysArray as $value) {
                $trailersArray->push(ArrayData::create((array)$data->{'results'}[$value]));
            }

            return $trailersArray;
        } catch (Throwable $e) {
            user_error('There was an issue connecting to the tmdb API: ' . $e);
        }

        return null;
    }

}
