<?php

namespace App\Catalogue\Jobs;

use Symbiote\QueuedJobs\Services\AbstractQueuedJob;
use Symbiote\QueuedJobs\Services\QueuedJob;

/**
 * An queued job which will pull out all the dataobjects in Catalogue::class and return the links
 * This will then spawn a job for each page::link() to curl all the posters and metadata
 *
 * @author Jared Dreyer <jaredkeithdreyer@gmail.com>
 */
class CrawlCatalogue extends AbstractQueuedJob implements QueuedJob
{

    public function setup(): void
    {
        $this->totalSteps = 1;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return 'Crawl entire catalogue to begin downloading of posters and metadata.';
    }

    public function process(): void
    {
        $catalogue = Catalogue::get();

        foreach ($catalogue as $page) {
            $this->addMessage('Queued job for page ' .$page->Title .' (#'. $page->ID .')');
            $poster = DataObject::get_by_id(Image::class, $page->PosterID);

            if ($poster !== false) {
                continue;
            }

            $unpublish = new CrawlMediaPage($page);
            singleton('QueuedJobService')->queueJob($unpublish);
        }

        $this->addMessage(sprintf(
            '%d Media pages are queued for jobs.',
            $catalogue->count()
        ));

        $this->currentStep = 1;
        $this->isComplete = true;
    }

    public function getSignature(): void
    {
        // TODO: Implement getSignature() method.
    }

    public function prepareForRestart(): void
    {
        // TODO: Implement prepareForRestart() method.
    }

    public function getJobType(): void
    {
        // TODO: Implement getJobType() method.
    }

    public function jobFinished(): void
    {
        // TODO: Implement jobFinished() method.
    }

    public function getJobData(): void
    {
        // TODO: Implement getJobData() method.
    }

    public function setJobData($totalSteps, $currentStep, $isComplete, $jobData, $messages): void
    {
        // TODO: Implement setJobData() method.
    }

    public function addMessage($message, $severity = 'INFO'): void
    {
        // TODO: Implement addMessage() method.
    }

}
