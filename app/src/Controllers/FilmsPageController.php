<?php

namespace App\Catalogue\PageTypes;

use App\Catalogue\Models\Catalogue;
use PageController;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ViewableData_Customised;

class FilmsPageController extends PageController
{

    private static array $allowed_actions = [
        'films',
    ];

    private static array $url_handlers = [
        'user/$ID' => 'films',
    ];

    /**
     * because SS3 is weird and needs index() to return something if
     * routing is in use.
     *
     * @todo update docblock
     * @return ViewableData_Customised
     */
    public function index(): ViewableData_Customised
    {
        return $this->films();
    }

    public function films()
    {
        // main SQL call
        $media = Catalogue::get()->filter([
            'Type' => 'movie',
            'OwnerID' => $this->slug,
        ])->sort('Title', 'ASC');

        $result = ArrayList::create();

        foreach ($media as $record) {
            $record->genres = $this->getFieldFiltersList($record->Genre, 'hidden') ?? null;
            $record->keywords = $this->getFieldFiltersList($record->Keywords, 'hidden') ?? null;
            $result->push($record);
        }

        return $this->customise([
            'movies' => $result,
        ]);
    }

}
