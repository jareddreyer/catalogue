<?php

namespace App\Catalogue\Jobs;

use App\Catalogue\Models\Catalogue;
use SilverStripe\Assets\Image;
use SilverStripe\ORM\ValidationException;
use Symbiote\QueuedJobs\Services\AbstractQueuedJob;
use Symbiote\QueuedJobs\Services\QueuedJob;
use Symbiote\QueuedJobs\Services\QueuedJobService;

/**
 * An queued job which will pull out all the dataobjects in Catalogue::class and return the links
 * This will then spawn a job for each page::link() to curl all the posters and metadata
 *
 * @author Jared Dreyer <jaredkeithdreyer@gmail.com>
 */
class CrawlCatalogue extends AbstractQueuedJob implements QueuedJob
{

    public function getTitle(): string
    {
        return 'Crawl entire catalogue to begin downloading of posters and metadata.';
    }

    /**
     * @throws ValidationException
     */
    public function process(): void
    {
        $catalogue = Catalogue::get();

        $queuedJobService = QueuedJobService::singleton();

        foreach ($catalogue as $page) {
            $this->addMessage('Queued job for page ' .$page->Title .' (#'. $page->ID .')');
            $poster = Image::get_by_id($page->PosterID);

            if ($poster !== null) {
                continue;
            }

            $job = new CrawlMediaPageJob();
            $job->hydrate($page);
            $queuedJobService->queueJob($job);
        }

        $this->addMessage(sprintf(
            '%d Media pages are queued for jobs.',
            $catalogue->count()
        ));

        $this->currentStep = 1;
        $this->isComplete = true;
    }

}
