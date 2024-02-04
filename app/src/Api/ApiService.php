<?php

namespace App\Catalogue\ApiServices;

use App\Catalogue\Api\Constants\Constants;
use App\Catalogue\Models\Catalogue;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use InvalidArgumentException;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Environment;
use SilverStripe\Core\Injector\Injector;
use stdClass;
use Throwable;

class ApiService
{
    use Configurable;

    private Client|null $guzzle;


    private string $metadataToken;

    private string $postersToken;

    private string $trailersToken;

    /**
     * Note: the omdb api uses entire URI as the endpoint and uses params for searches.
     *
     * Endpoint domains for:
     * @link https://www.omdbapi.com Metadata API
     * @link https://api.themoviedb.org trailers API
     * @link https://img.omdbapi.com Poster images API
     * @var string[]
     */
    private static array $api_base_uri = [
        'trailers' => 'https://api.themoviedb.org/3/',
        'metadata' => 'https://www.omdbapi.com/',
        'posters' => 'https://img.omdbapi.com/',
    ];

    /**
     * Endpoints for themoviedb.org
     *
     * @link https://api.themoviedb.org trailers API
     * @var string[]
     */
    private static array $tmdb_api_endpoints = [
        'find' => 'find',
        'movie' => 'movie',
        'tv' => 'tv',
        'videos' => 'videos',
    ];

    /**
     * Set up the http client and client-id and client-secret from environment variables to be used by the http client
     * for making requests to the OMDB API.
     */
    public function __construct(?callable $handler = null)
    {
        // Get all our tokens and validate they are set.
        $this->metadataToken = Environment::getEnv('METADATA_API_KEY');
        $this->trailersToken = Environment::getEnv('TRAILERS_API_KEY');
        $this->postersToken = Environment::getEnv('POSTERS_API_KEY');
        $this->validateInit();

        // Setup handler stack for our unit tests.
        $stack = HandlerStack::create($handler);

        $this->guzzle = new Client([
            'timeout' => self::config()->get('request_timeout'),
            'handler' => $stack,
        ]);
    }

    /**
     * Gets metadata from the OMDB API via Title or IMDB ID look up.
     * e.g.
     * request => ?ImdbID='tt4154756'.
     * request => ?i=The Dark Knight.
     *
     * @param string[] $query - endpoint and params.
     * @throws NotFoundExceptionInterface|RuntimeException
     */
    public function getMetadata(array $query): stdClass|null
    {
        $params = $this->generateQuery($query);
        $uri = sprintf('%s?apikey=%s&%s', self::$api_base_uri['metadata'], $this->metadataToken, $params);

        // Initialize $error for use in error logging.
        $error = null;

        try {
            // Request to OMDB Api
            $response = $this->guzzle->get($uri);

            // Decode the response
            $result = $this->decodeResponse($response);

            // Check we're not getting a response that returned an error.
            if ($result->Response === 'False') {
                $error = match ($result->Error) {
                    'Incorrect IMDb ID.' => [
                        'title' => 'Incorrect IMDb ID.',
                        'message' => 'IMDB ID does not exist, you may have entered it incorrectly to the catalogue.',
                        'code' => 404,
                    ],
                    'Invalid API key!' => [
                        'title' => 'Invalid API key!',
                        'message' => 'Could not connect to omdbapi.com Api, requires authorization key.',
                        'code' => 401,
                    ],
                    'Movie not found!' => [
                        'title' => 'Movie not found!',
                        'message' => 'Title was not found response. You may have entered it incorrectly into the catalogue.',
                        'code' => 404,
                    ],
                    default => [
                        'title' => 'Something went wrong.',
                        'message' => 'Check php logs.',
                        'code' => 404,
                    ],
                };

                throw new RuntimeException($error['title'], $error['code']);
            }
        } catch (Throwable $exception) {
            // Log any exceptions
            static::log_error(
                $exception,
                [
                    'request' => [
                        'api_url' => self::$api_base_uri['metadata'],
                        'endpoint' => $params,
                    ],
                    'response' => $result ?? '',
                    'additional_info' => $error['message'],
                ]
            );

            return null;
        }

        return $result;
    }

    /**
     * Allows saving of imdb posters to local storage
     * @throws NotFoundExceptionInterface
     */
    public function getPosterImage(string $uri): StreamInterface|null
    {
        if ($uri === 'N/A') {
            static::log_error(
                new RuntimeException('No poster available', '404'),
                ['request' => $uri]
            );

            return null;
        }

        try {
            $response = $this->guzzle->get($uri);

            return $response->getBody();
        } catch (Throwable $exception) {
            // Log any exceptions
            static::log_error(
                $exception,
                [
                    'request' => $uri,
                    'response' => $response ?? '',
                ]
            );

            return null;
        }
    }

    /**
     * Look up {@link self::$api_base_uri['trailers']} to get their internal
     * title identifier for further use, i.e. get this titles' video trailers.
     *
     * @throws NotFoundExceptionInterface
     */
    public function getTmdbID(string $id, array $query): stdClass|null
    {
        $params = $this->generateQuery($query);

        $uri = sprintf(
            '%s%s/%s?%s&api_key=%s',
            self::$api_base_uri['trailers'],
            self::$tmdb_api_endpoints['find'],
            $id,
            $params,
            $this->trailersToken
        );

        // get ID from tmddb.org
        try {
            // Request to OMDB Api
            $response = $this->guzzle->get($uri);

            // Decode the response
            $result = $this->decodeResponse($response);
        } catch (Throwable $exception) {
            // Log any exceptions
            static::log_error(
                $exception,
                [
                    'request' => [
                        'api_url' => self::$api_base_uri['trailers'],
                        'endpoint' => $params,
                    ],
                    'response' => $result ?? '',
                ]
            );

            return null;
        }

        return $result;
    }

    /**
     * returns data for trailers from themoviedb.org
     * @todo needs a service and tidying up
     */
    public function getTrailers(string $id, string $type, array $query): stdClass|null
    {
        $params = $this->generateQuery($query);
        $uri = sprintf(
            '%s%s/%s/%s?api_key=%s&%s',
            self::$api_base_uri['trailers'],
            $type === 'movie' ? self::$tmdb_api_endpoints['movie'] : self::$tmdb_api_endpoints['tv'],
            $id,
            self::$tmdb_api_endpoints['videos'],
            $this->trailersToken,
            $params,
        );

        // now get trailers from id
        try {
            // Request to tmdb Api
            $response = $this->guzzle->get($uri);

            // Decode the response
            $result = $this->decodeResponse($response);
        } catch (Throwable $exception) {
            // Log any exceptions
            static::log_error(
                $exception,
                [
                    'request' => [
                        'api_url' => self::$api_base_uri['metadata'],
                        'endpoint' => $params,
                    ],
                    'response' => $result ?? '',
                ]
            );

            return null;
        }

        return $result;
    }

    /**
     * Log error using the format for logging exceptions to Raygun
     *
     * @param string[] $data
     *      - request (additional request data)
     *      - response (additional response data)
     *      - data (additional custom data)
     * @param string[] $tags - useful if you want to record this to Raygun.
     * @throws NotFoundExceptionInterface
     */
    public static function log_error(?Throwable $exception, ?array $data = [], ?array $tags = []): void
    {
        $logger = Injector::inst()->get(LoggerInterface::class);
        $logger->error(
            $exception->getMessage(),
            array_merge([
                'exception' => $exception,
                'tags' => $tags,
            ], $data)
        );
    }

    /**
     * Helper method returns encoded query string
     *
     * @param string[] $params
     */
    public function generateQuery(array $params): ?string
    {
        if (count($params) === 0) {
            return null;
        }

        return http_build_query($params);
    }

    /**
     * This function returns an HTTPResponse_Exception based on the details of the passed Throwable instance.
     * These errors can be handled accordingly in the frontend (JS) by fetch mechanisms.
     *
     * @param Throwable $e
     * @throws HTTPResponse_Exception
     */
    public function createHTTPErrorFromException(Throwable $e): HTTPResponse_Exception|null
    {
        // We don't want to output any sensitive data (such as server technology details etc.) to the UI.
        // This array contains codes which are potentially coupled with messages containing sensitive data, to allow
        // us to handle error responses with these codes differently.
        $sensitiveDataCodes = [400];
        $code = $e->getCode();

        if (in_array($code, $sensitiveDataCodes)) {
            return Controller::curr()->httpError($code, Constants::DEFAULT_HTTP_BAD_REQUEST_MESSAGE);
        }

        $message = $e->getMessage() ?? Constants::DEFAULT_HTTP_ERROR_MESSAGE;
        $response = method_exists($e, 'getResponse') ? $e->getResponse() : null;

        if ($response) {
            $responseBody = json_decode($response->getBody());
            $message = $responseBody->message ?? $message;
        }

        return Controller::curr()->httpError($code, $message);
    }

    public function decodeResponse(ResponseInterface $response): string|stdClass
    {
        $contents = (string) $response->getBody();

        if ($contents === '') {
            return '';
        }

        $decodedResponse = json_decode($contents);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException( Constants::DEFAULT_HTTP_ERROR_DECODING . json_last_error_msg());
        }

        return $decodedResponse;
    }

    /**
     * Builds an array of parameters for querying metadata based on the catalogue record data.
     *
     * @param Catalogue $record - Catalogue DataObject
     * @param string $plot - can be 'short' or 'full'
     * @return string[] An array of parameters for querying metadata.
     *
     * @example
     *   $encodedTitle = urlencode('The Dark Knight');
     *   $videoType = 'movie';
     *   $year = '2008';
     *   $imdbID = 'tt0468569';
     *   // Resulting $params array when $imdbID is provided:
     *   // [
     *   //     'i' => 'tt0468569',
     *   //     'type' => 'movie',
     *   //     'plot' => 'full',
     *   //     'y' => '2008',
     *   // ]
     *
     * @example
     *   $encodedTitle = urlencode('Inception');
     *   $videoType = 'movie';
     *   $params = buildMetadataParams($encodedTitle, $videoType);
     *   // Resulting $params array:
     *   // [
     *   //     't' => 'Inception',
     *   //     'type' => 'movie',
     *   //     'plot' => 'full',
     *   // ]
     *
     * @see https://www.omdbapi.com/
     *
     * Note: ApiToken is injected to the query string before this inclusion of this array.
     */
    public static function buildMetadataQueryParams(Catalogue $record, string $plot = 'full'): array
    {
        // Default look up via title search
        $query = [
            't' =>  $record->Title,
            'type' => $record->Type,
            'plot' => $plot,
        ];

        // Include Year param
        if ($record->Year !== null) {
            $query['y'] = $record->Year;
        }

        // Remove title lookup and use IMDB identifier for lookup.
        if ($record->ImdbID !== null) {
            unset($query['t']);
            $query['i'] = $record->ImdbID;
        }

        return $query;
    }

    /**
     * Simple helper to return a json response
     *
     * @param string[] $body message body
     */
    private function jsonResponse(array $body, int $statusCode): HTTPResponse
    {
        return
            HTTPResponse::create()
                ->addHeader('Content-Type', 'application/json')
                ->setStatusCode($statusCode)
                ->setBody(json_encode($body, JSON_UNESCAPED_SLASHES));
    }

    /**
     * Validates the env vars needed for all the APi's
     * and throws exceptions if they are not set.
     */
    private function validateInit(): void
    {
        if (!$this->metadataToken) {
            throw new InvalidArgumentException('OMDB api metadata token not found');
        }

        if (!$this->trailersToken) {
            throw new InvalidArgumentException('TMDB api token not found');
        }

        if (self::config()->get('enable_posters_api') === true) {
            if (!$this->postersToken) {
                throw new InvalidArgumentException('OMDB api posters token not found');
            }
        }
    }

}
