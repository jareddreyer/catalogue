<?php

namespace App\Catalogue\Jobs;

use App\Catalogue\ApiServices\ApiService;
use App\Catalogue\Models\Catalogue;
use PageController;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\ORM\ValidationException;
use Symbiote\QueuedJobs\Services\AbstractQueuedJob;
use Symbiote\QueuedJobs\Services\QueuedJob;

/**
 * An queued job which will curl page::link() to download all the posters and metadata to local records.
 * @todo Needs updating to use service.
 * @author Jared Dreyer <jaredkeithdreyer@gmail.com>
 */
class HydrateCatalogueRecordJob extends AbstractQueuedJob implements QueuedJob
{

    public function hydrate(Catalogue $record): void
    {
        $this->record = $record;
    }

    public function setup(): void
    {
        $this->totalSteps = 1;
    }

    public function getTitle(): string
    {
        return 'Crawling  "'. $this->record->Title . '" to begin fetching metadata and saving the poster.';
    }

    /**
     * @throws HTTPResponse_Exception
     * @throws NotFoundExceptionInterface
     * @throws ValidationException
     */
    public function process(): void
    {
        // rebind this to keep the identifier member easier to use.
        $record = $this->record;

        $profilePageLink = PageController::singleton()->getProfileURL();
        $link = Controller::join_links($profilePageLink, 'title/', $record->ID);

        $this->addMessage('Crawling metadata assets for "'. $record->Title . '"(#' . $record->ID .')');

        // Grab our service build a request and then call the OMDB Api.
        $service = new ApiService();
        $query = $service::buildMetadataQueryParams($record);
        $response = $service->getMetadata($query);

        // Fail the job if no metadata response.
        if ($response === null) {
            throw new RuntimeException('Catalogue record ' . '(#'.$record->ID . ') failed to fetch, check logs.');
        }

        $this->addMessage('Metadata response:');
        $this->addMessage(json_encode($response));

        // Hydrate with the metadata
        $record->hydrateMetadataFromResponse($response);

        // Hydrate with the poster
        $posterImageSrc = $service->getPosterImage($response->Poster);

        // Fail the job if no poster response.
        if ($posterImageSrc === null) {
            throw new RuntimeException('Poster failed to return a response. Check logs.');
        }

        // Hydrate catalogue record and get our poster.
        $record->hydratePosterFromResponse($response, $posterImageSrc);
        $this->addMessage('Poster image has been created.');

        $this->addMessage('Catalogue record ' . '(#'.$record->ID .') is updated.');
        $this->addMessage($record->Title . ' can be viewed at ' . $link );

        $this->currentStep = 1;
        $this->isComplete = true;
    }

}
