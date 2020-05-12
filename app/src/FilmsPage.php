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
                    'Type'=>'film',
                    'OwnerID' => $this->slug
                ])
            ->sort('Title', 'ASC');

        $result = ArrayList::create();

        foreach ($media as $record) {
            $record->genres = $this->getFieldFiltersList('genres', $record->Genre);
            $record->keywords = $this->getFieldFiltersList('keywords', $record->Keywords);
            $result->push($record);
        }

        return $this->customise(
            [
                'films' => $result
            ]
        );
	}
}
