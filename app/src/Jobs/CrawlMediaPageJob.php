<?php

namespace App\Catalogue\Jobs;

use App\Catalogue\Models\Catalogue;
use Page;
use PageController;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use Symbiote\QueuedJobs\Services\AbstractQueuedJob;
use Symbiote\QueuedJobs\Services\QueuedJob;

/**
 * An queued job which will curl page::link() to download all the posters and metadata to local records.
 * @todo Needs updating to use service.
 * @author Jared Dreyer <jaredkeithdreyer@gmail.com>
 */
class CrawlMediaPageJob extends AbstractQueuedJob implements QueuedJob
{

    public function hydrate(Catalogue $record): void
    {
        $this->record = $record;
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
        return 'Crawling  '. $this->record->Title . ' to begin downloading of posters and metadata.';
    }

    public function process(): void
    {
        $profilePageLink = PageController::singleton()->getProfileURL();
        $link = Controller::join_links($profilePageLink, 'title/', $this->record->ID);

        $this->addMessage(
            'Crawling media '. $link . ' for media: '. $this->record->Title . '(#'.$this->record->ID .')'
        );

        $request = new HTTPRequest(
            'GET',
            $link
        );

        $this->currentStep = 1;
        $this->isComplete = true;
    }

}
