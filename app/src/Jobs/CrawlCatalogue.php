<?php

/**
 * An queued job which will pull out all the dataobjects in Catalogue::class and return the links
 * This will then spawn a job for each page::link() to curl all the posters and metadata
 *
 * @author Jared Dreyer <jaredkeithdreyer@gmail.com>
 */
class CrawlCatalogue extends AbstractQueuedJob implements QueuedJob {

    public function setup() {
        $this->totalSteps = 1;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return "Crawl entire catalogue to begin downloading of posters and metadata.";
    }

    public function process()
    {
        $catalogue = Catalogue::get();

        foreach ($catalogue as $page) {
            $this->addMessage('Queued job for page ' .$page->Title .' (#'. $page->ID .')');

            $unpublish = new CrawlMediaPage($page);
            singleton('QueuedJobService')->queueJob($unpublish);

        }

        $this->addMessage(sprintf("%d Media pages are queued for jobs.",
            $catalogue->count()
            ));

        $this->currentStep = 1;
        $this->isComplete = true;
    }
}
