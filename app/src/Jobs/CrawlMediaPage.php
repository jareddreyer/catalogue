<?php

/**
 * An queued job which will curl page::link() to download all the posters and metadata to local records.
 *
 * @author Jared Dreyer <jaredkeithdreyer@gmail.com>
 */
class CrawlMediaPage extends AbstractQueuedJob implements QueuedJob
{

    /**
     * CrawlPageJob constructor.
     * @param page $page

     */
    public function __construct($page=null)
    {
        if ($page) {
            $this->page = $page;
        }
    }

    public function setup() {
        $this->totalSteps = 1;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Crawling  '. $this->page->Title . ' to begin downloading of posters and metadata.';
    }

    public function process()
    {
        $domain = Director::absoluteBaseURL();
        $profilePage = Page_Controller::create();

        $this->addMessage('Crawling media '. $domain . $profilePage->getProfileURL() . 'title/'.$this->page->ID. ' for media: '. $this->page->Title . '(#'.$this->page->ID .')');

        $service = new RestfulService($domain . $profilePage->getProfileURL() . 'title/'.$this->page->ID, 0);
        $service->request();

        $this->currentStep = 1;
        $this->isComplete = true;
    }
}
