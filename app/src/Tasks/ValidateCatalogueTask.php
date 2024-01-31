<?php

namespace App\Catalogue\Tasks;

use Bramus\Monolog\Formatter\ColoredLineFormatter;
use Exception;
use Monolog\Handler\FilterHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\BuildTask;
use Symbiote\QueuedJobs\Services\QueuedJob;
use Symbiote\QueuedJobs\Services\QueuedJobService;

class ValidateCatalogueTask extends BuildTask
{
    protected $title = 'Validate Catalogue items';

    protected $description = 'This task will check all broken jobs for a catalogue item that failed to fetch correctly from OMDB Api.';

    private LoggerInterface $logger;

    private static string $segment = 'ValidateCatalogue';

    private static array $dependencies = [
        'Logger' => '%$' . LoggerInterface::class,
    ];

    /**
     * @inheritDoc
     * @throws Exception|NotFoundExceptionInterface
     */
    public function run($request): void
    {
        $this->addLogHandlers();

        // Get the video title from the Catalogue model.
        $record = QueuedJobService::singleton()->getJobList()->filter(['JobStatus' => QueuedJob::STATUS_BROKEN]);

        $this->logger->notice('######################################################');
        $this->logger->notice('Validating Catalogue crawl jobs');
        $this->logger->notice('######################################################');

        foreach ($record as $job) {
            $this->logger->info('Job ' . $job->Title . ' (#' . $job->ID . ') has failed');
            $this->logger->debug(@unserialize($job->SavedJobMessages)[2]);
            $link = sprintf(
                'http://catalogue.test/admin/queuedjobs/Symbiote-QueuedJobs-DataObjects-'
                . 'QueuedJobDescriptor/EditForm/field/QueuedJobDescriptor/item/%s/edit',
                $job->ID,
            );
            $this->logger->info('Link : ' . $link);
        }
    }

    public function setLogger(LoggerInterface $logger): ValidateCatalogueTask
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
