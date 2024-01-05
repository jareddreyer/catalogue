<?php

namespace App\Catalogue\Jobs;

use Symbiote\QueuedJobs\Services\AbstractQueuedJob;
use Symbiote\QueuedJobs\Services\QueuedJob;

/**
 * An queued job which will curl page::link() to download all the posters and metadata to local records.
 *
 * @author Jared Dreyer <jaredkeithdreyer@gmail.com>
 */
class CrawlMediaPage extends AbstractQueuedJob implements QueuedJob
{

    /**
     * CrawlPageJob constructor.
     *
     * @param page $page
     */
    public function __construct(?page $page = null)
    {
        if (!$page) {
            return;
        }

        $this->page = $page;
    }

    public function setup(): void
    {
        $this->totalSteps = 1;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return 'Crawling  '. $this->page->Title . ' to begin downloading of posters and metadata.';
    }

    public function process(): void
    {
        $domain = Director::absoluteBaseURL();
        $profilePage = Page_Controller::create();

        $this->addMessage(
            'Crawling media '. $domain . $profilePage->getProfileURL() . 'title/'.$this->page->ID. ' for media: '. $this->page->Title . '(#'.$this->page->ID .')'
        );

        $service = new RestfulService($domain . $profilePage->getProfileURL() . 'title/'.$this->page->ID, 0);
        $service->request();

        $this->currentStep = 1;
        $this->isComplete = true;
    }

}
