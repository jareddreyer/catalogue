<?php

namespace App\Catalogue\Api\Constants;

/**
 * A central location to define commonly used constants on the app.
 */
class Constants
{

    /**
     * Error messaging
     */
    public const DEFAULT_HTTP_ERROR_MESSAGE = 'An error has occurred. Please try again later.';
    public const DEFAULT_HTTP_UNAUTHORISED_MESSAGE = 'You are not authorised to perform this task.';
    public const DEFAULT_HTTP_BAD_REQUEST_MESSAGE = 'Unable to process this request';
    public const DEFAULT_HTTP_ERROR_DECODING = 'Error trying to decode API response: ';
    public const CATALOGUE_ID_DOES_NOT_EXIST = 'The title you are querying does not exist.';

}
