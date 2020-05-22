<?php
class TelevisionPage extends Page
{
    private static $allowed_children = array();

}

class TelevisionPage_Controller extends Page_Controller
{
    private static $allowed_actions = array (
        'television'
    );

    private static $url_handlers = array(
        'user/$ID' => 'television'
    );

    public function init()
    {
        parent::init();
    }

    /**
     * because SS3 is weird and needs index() to return something if
     * routing is in use.
     * @return ViewableData_Customised
     */
    public function index()
    {
        return $this->television();
    }

    public function television()
    {
        // main SQL call
        $media = Catalogue::get()
            ->filter(
                [
                    'Type'      => 'series',
                    'OwnerID'   => $this->slug
                ])
            ->sort('Title', 'ASC');

        $result = ArrayList::create();

        foreach ($media as $record) {
            $record->genres = $this->getFieldFiltersList($record->Genre, 'hidden');
            $record->keywords = $this->getFieldFiltersList($record->Keywords, ' hidden');
            $result->push($record);
        }

        return $this->customise(
            [
                'television' => $result
            ]
        );
    }
}
