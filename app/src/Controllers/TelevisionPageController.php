<?php

namespace App\Catalogue\PageTypes;

use App\Catalogue\Models\Catalogue;
use PageController;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ViewableData_Customised;

class TelevisionPageController extends PageController
{

    private static array $allowed_actions = [
        'television',
    ];

    private static array $url_handlers = [
        'user/$ID' => 'television',
    ];

    /**
     * because SS3 is weird and needs index() to return something if
     * routing is in use.
     *
     * @todo update this docblock
     * @return ViewableData_Customised
     */
    public function index(): ViewableData_Customised
    {
        return $this->television();
    }

    public function television()
    {
        // main SQL call
        $media = Catalogue::get()->filter([
            'Type' => 'series',
            'OwnerID' => $this->slug,
        ])->sort('Title', 'ASC');

        $result = ArrayList::create();

        foreach ($media as $record) {
            $record->genres = $this->getFieldFiltersList($record->Genre, 'hidden') ?? null;
            $record->keywords = $this->getFieldFiltersList($record->Keywords, ' hidden') ?? null;
            $result->push($record);
        }

        return $this->customise([
            'television' => $result,
        ]);
    }

}
