<?php

namespace App\Catalogue\Tasks;

use App\Catalogue\ApiServices\ApiService;
use App\Catalogue\Models\Catalogue;
use Bramus\Monolog\Formatter\ColoredLineFormatter;
use Exception;
use Monolog\Handler\FilterHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PageController;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\BuildTask;

class HydrateCatalogueRecord extends BuildTask
{
    protected $title = 'Hydrate Catalogue record by ID';

    protected $description = 'This task will take an ID from the catalogue and request OMDB Api to hydrate it.';

    private LoggerInterface $logger;

    private static string $segment = 'HydrateCatalogueRecord';

    private static array $dependencies = [
        'Logger' => '%$' . LoggerInterface::class,
    ];

    /**
     * @inheritDoc
     * @throws Exception|NotFoundExceptionInterface
     */
    public function run($request)
    {
        $this->addLogHandlers();

        $id = $request->getVar('id');

        if (!$id) {
            $this->logger->warning( 'No catalogue ID was set. No request will be sent.');
            return;
        }

        // Get the video title from the Catalogue model.
        $record = Catalogue::get_by_id($id);
        $profilePageLink = PageController::singleton()->getProfileURL();
        $link = Controller::join_links($profilePageLink, 'title', $record->ID);

        $this->logger->notice('######################################################');
        $id = $record->ImdbID ?? '#' . $record->ID;
        $this->logger->notice(
            'Hydrating "' . $record->Title . ' (' . $id . ')" from OMDB Api:'
        );
        $this->logger->notice('######################################################');

        // Validate we're not hydrating a record that exists already
        if ($record->Metadata()->exists() && $request->getVar('force') !== 'true'){
            $this->logger->info( "Catalogue record {$record->Title} ({$record->ImdbID}) is already fetched and hydrated.");
            return;
        }

        // Grab our service build a request and then call the OMDB Api.
        $service = new ApiService();
        $query = $service::buildMetadataQueryParams($record);
        $response = $service->getMetadata($query);

        if ($response === null) {
            // Our log handler will already have captured any runtimeexceptions
            // So gracefully exit.
            return;
        }

        $this->logger->info('Metadata response:');
        $this->logger->info(json_encode($response));

        // Hydrate with the metadata
        $record->hydrateMetadataFromResponse($response);

        // Hydrate with the poster
        $posterImageSrc = $service->getPosterImage($response->Poster);

        if ($posterImageSrc === null) {
            // Our log handler will already have captured any runtimeexceptions
            // So gracefully exit.
            return;
        }

        $record->hydratePosterFromResponse($response, $posterImageSrc);

        $this->logger->info('Catalogue record ' . '(#'.$record->ID .') is updated.');
        $this->logger->notice($record->Title . ' can be viewed at ' . $link );
    }

    public function setLogger(LoggerInterface $logger): HydrateCatalogueRecord
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @throws Exception|NotFoundExceptionInterface
     */
    protected function addLogHandlers()
    {
        // Using a global service here so other systems can control and redirect log output,
        // for example when this task is run as part of a queuedjob
        $logger = Injector::inst()->get(LoggerInterface::class);

        if ($logger) {
            $formatter = new ColoredLineFormatter();
            $formatter->ignoreEmptyContextAndExtra();

            $errorHandler = new StreamHandler('php://stderr', Logger::ERROR);
            $errorHandler->setFormatter($formatter);

            $standardHandler = new StreamHandler('php://stdout');
            $standardHandler->setFormatter($formatter);

            // Avoid double logging of errors
            $standardFilterHandler = new FilterHandler(
                $standardHandler,
                Logger::DEBUG,
                Logger::WARNING
            );

            $logger->pushHandler($standardFilterHandler);
            $logger->pushHandler($errorHandler);
        }
    }
}
