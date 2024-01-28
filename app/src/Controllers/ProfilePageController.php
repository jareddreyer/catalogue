<?php

namespace App\Catalogue\PageTypes;

use App\Catalogue\Api\Constants\Constants;
use App\Catalogue\ApiServices\ApiService;
use App\Catalogue\Models\Catalogue;
use PageController;
use Psr\Container\NotFoundExceptionInterface;
use SilverStripe\Assets\Image;
use SilverStripe\Control\HTTPResponse_Exception;
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
     * @throws HTTPResponse_Exception|NotFoundExceptionInterface|ValidationException
     *
     * @todo tidy up how profile => $record is set
     * @todo needs tidying up for early exists
     */
    public function profile(): DBHTMLText|ViewableData_Customised|null
    {
        // get db record
        $title = Catalogue::get_by_id($this->getCatalogueSlug());

        if (!$title) {
            $this->httpError('404', Constants::CATALOGUE_ID_DOES_NOT_EXIST);
        }

        // Get our metadata first before returning the catalogue DBO
        // This will generate our poster image.
        $metadata = $this->getMetadata();

        // Reload Catalogue DBO.
        $title = Catalogue::get_by_id($this->getCatalogueSlug());

        foreach ($title as $record) {
            $record->genres = $this->getFieldFiltersList($record->Genre, 'badge filters');
            $record->keywords = $this->getFieldFiltersList($record->Keywords, 'badge filters');
        }

        $data = [
            'profile' => $record,
            'metadata' => $metadata,
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
     * .
     *
     * @return ArrayList
     * @throws ValidationException
     * @throws HTTPResponse_Exception
     */

    /**
     * Creates metadata file from the OMDB API.
     * This function will also create relationships and save a local file
     *
     * @throws HTTPResponse_Exception|NotFoundExceptionInterface|ValidationException
     */
    public function getMetadata(): ArrayList|stdClass|null
    {
        // Get the video title from the Catalogue model.
        $catalogueItem = Catalogue::get_by_id($this->getCatalogueSlug());

        // Early exit if this ID does not exist
        if (!$catalogueItem) {
            $this->httpError('404', Constants::CATALOGUE_ID_DOES_NOT_EXIST);
        }

        $result = ArrayList::create();

        if ($catalogueItem->Metadata()->exists()){
            $data = json_decode($catalogueItem->Metadata->getString());
            $result->push(ArrayData::create($this->jsonDataToArray($data)));

            return $result;
        }

        // Grab our service build a request and then call the OMDB Api.
        $service = new ApiService();
        $query = $service::buildMetadataQueryParams($catalogueItem);
        $data = $service->getMetadata($query);

        // Hydrate our catalogue record and build assets.
        $catalogueItem->hydrateMetadataFromResponse($data);

        // Push result into array list
        $result->push(ArrayData::create($this->jsonDataToArray($data)));

        // Update the Poster at same time so it does not need to be done separately.
        $this->getPoster($data);

        return $result;
    }

    /**
     * @throws HTTPResponse_Exception|NotFoundExceptionInterface|ValidationException
     */
    public function getPoster(stdClass $metadata): Image|stdClass|null
    {
        // Get the video title from the Catalogue model.
        $catalogueItem = Catalogue::get_by_id($this->getCatalogueSlug());

        // Early exit if this ID does not exist
        if (!$catalogueItem) {
            $this->httpError('404', Constants::CATALOGUE_ID_DOES_NOT_EXIST);
        }

        if ($catalogueItem->Poster()->exists()){
            return $catalogueItem->Poster;
        }

        // Grab our service build a request and then call OMDB Api.
        $service = new ApiService();
        $posterImageSrc = $service->getPosterImage($catalogueItem->PosterURL);
        $catalogueItem->hydratePosterFromResponse($metadata, $posterImageSrc);

        return $catalogueItem->Poster;
    }

    /**
     * We need to return the IMDBAPI result as an arraylist for the template.
     *
     * @todo refactor into a foreach loop so don't have specify all field names
     * @param string[] $data - api result
     */
    public function jsonDataToArray(stdClass $data): ?array
    {
        return [
            'Title' => $data->Title ?? '',
            'Year' => $data->Year ?? '',
            'Rated' => $data->Rated ?? '',
            'Released' => $data->Released ?? '',
            'Runtime' => $data->Runtime ?? '',
            'Genre' => $data->Genre ?? '',
            'Director' => $data->Director ?? '',
            'Writer' => $data->Writer ?? '',
            'Actors' => $data->Actors ?? '',
            'Plot' => $data->Plot ?? '',
            'Language' => $data->Language ?? '',
            'Country' => $data->Country ?? '',
            'Awards' => $data->Awards ?? '',
            'Poster' => $data->Poster ?? '',
            'Ratings' => $data->Ratings ?? '',
            'Metascore' => $data->Metascore ?? '',
            'imdbRating' => $data->imdbRating ?? '',
            'imdbVotes' => $data->imdbVotes ?? '',
            'imdbID' => $data->imdbID ?? '',
        ];
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
