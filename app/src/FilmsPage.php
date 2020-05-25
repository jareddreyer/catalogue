<?php
class FilmsPage extends Page
{

}

class FilmsPage_Controller extends Page_Controller
{
    private static $allowed_actions = [
        'movies'
    ];

    private static $url_handlers = [
        'user/$ID' => 'movies'
    ];

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
        return $this->movies();
    }

	public function movies()
	{
        // main SQL call
        $media = Catalogue::get()
            ->filter(
                [
                    'Type'      => 'movie',
                    'OwnerID'   => $this->slug
                ])
            ->sort('Title', 'ASC');

        $result = ArrayList::create();

        foreach ($media as $record) {
            $record->genres = $this->getFieldFiltersList($record->Genre, 'hidden');
            $record->keywords = $this->getFieldFiltersList($record->Keywords,'hidden');
            $result->push($record);
        }

        return $this->customise(
            [
                'movies' => $result
            ]
        );
	}
}
