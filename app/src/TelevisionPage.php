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

        //jplist css
        Requirements::themedCSS('jplist.core.min');
        Requirements::themedCSS('jplist.textbox-filter.min');
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
            $record->genres = $this->getFieldFiltersList('genres', $record->Genre);
            $record->seasonLinks = str_replace('Season', '', $record->Seasons);
            $record->keywords = $this->getFieldFiltersList('keywords', $record->Keywords);
            $result->push($record);
        }

        return $this->customise(
            [
                'television' => $result
            ]
        );
    }





}
