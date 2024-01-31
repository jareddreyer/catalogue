<?php

namespace App\Catalogue\Jobs;

use App\Catalogue\Models\Catalogue;
use SilverStripe\ORM\FieldType\DBDatetime;
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
class CrawlCatalogueJob extends AbstractQueuedJob implements QueuedJob
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
        $queuedJobService = QueuedJobService::singleton();
        $reviewDate = DBDatetime::now()->modify('- 6 months')->Format(DBDatetime::ISO_DATETIME);

        $this->addMessage('Review date is set to: ' . $reviewDate);

        $catalogue = Catalogue::get()->filter([
            'MarkAsIncomplete' => true,
        ]);

        // Setup all our counts.
        $hardLimit = 600;
        $counter = 0;
        $totalChunks = ceil($catalogue->count() / $hardLimit);

        foreach ($catalogue as $page) {
            $this->addMessage('Queued job for page ' .$page->Title .' (#'. $page->ID .')');

            if ($page->Metadata->exists()) {
                continue;
            }

//            $lastFetched = DBDate::create()->setValue($page->LastFetched);
//            if ($lastFetched->timeDiffIn('months') < 6 ) {
//                continue;
//            }

            $job = new HydrateCatalogueRecordJob();
            $job->hydrate($page);
            $queuedJobService->queueJob(
                $job,
            );
        }

        $this->addMessage(sprintf(
            '%d Catalogue records queued for jobs.',
            $catalogue->count()
        ));

        $this->currentStep = 1;
        $this->isComplete = true;
    }

    private function getCatalogueInChunks(int $chunkSize)
    {
        $catalogue = Catalogue::get();
        $query = $catalogue->limit($chunkSize);

        while ($chunk = $query) {
            foreach ($chunk as $item) {
                yield $item;
            }

            if ($chunk->count() === 0) {
                break;
            }

            return $item;
        }
    }

}
