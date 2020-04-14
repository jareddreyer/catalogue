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
        return $this->movies();
    }

	public function movies()
	{
        $keywords = $this->getKeywords();

        Requirements::customScript('        
            var availableKeywords = [
            '.$keywords.'
            ];
            $(function() {
     
                $(".keywordsText").autocomplete({
                    source: availableKeywords,
                    minLength: 3,
                    select: function(event, ui) {
                        $(".keywordsText").trigger("input");
                    },
             
                    html: true, // optional (jquery.ui.autocomplete.html.js required)
                    
                });
             
            });
         ');

        // main SQL call
	    $sqlQuery = "SELECT Catalogue.*, Member.ID as MID, Member.Email, Member.FirstName, Member.Surname 
                     FROM Catalogue 
                     LEFT JOIN Member ON Catalogue.Owner = Member.ID 
                     WHERE Catalogue.VideoType = 'film' 
                     AND Catalogue.Owner = $this->slug
                     ORDER BY Catalogue.VideoTitle";

        $records = DB::query($sqlQuery);

        if ($records)
        {
            $set = ArrayList::create();

            foreach ($records as $record)
            {
                $record['lastupdatedreadable'] = parent::humanTiming($record['LastEdited']);
                $record['genres'] = $this->listFilmGenres($record['Genre']);
                $record['posters'] = $this->postersAssetsFolderName;
                $record['profileLink'] = parent::getProfileURL();

                $set->push(ArrayData::create($record));
            }

            return $this->customise(['movies' => $set]);
        }
	}

    /**
     * Takes genres string element and splits them into array element for each genre
     *
     * @param string
     * @return string
     */
    private function listFilmGenres ($genre)
    {
        $explode = explode("|", $genre); //explode string to array by delimiter

        $listoption = "";
        foreach ($explode as $value)
        {
            $listoption .= '<span class="hide genre '.str_replace(' ', '', $value).'">'.$value.'</span>';
        }

        return $listoption;

    }

    /**
     * gets keywords as a separate query
     * sorts and removes duplicates
     * @return array
     */
    public function getKeywords()
    {
        $result = Catalogue::get()->sort('Keywords')->where('Keywords is not null')->column("Keywords");

        if($result != null)
        {

            /** clean up keywords from DB **/
            $_list = array(parent::convertAndCleanList($result, ','));

            $listoption = "";
            foreach($_list as $list)
            {
                foreach ($list as $value)
                {
                    $listoption .= '"'. $value.'",';
                }
            }

            return $listoption;
        }
    }


    /**
     * gets genres as a separate query sorts and removes duplicates
     *
     * @return string
     */
    public function getGenres()
    {
        $result = Catalogue::get()->sort('Genre')->where('Genre is not null')->column("Genre");

        if($result != null)
        {

            /** clean up keywords from DB **/
            $_list = array(parent::convertAndCleanList($result, '|'));

            $genreList = "";
            foreach($_list as $list)
            {
                foreach ($list as $value)
                {
                    $genreList .= "<li><span data-path=\".".str_replace(' ', '', $value)."\">".$value."</span></li>";
                }
            }

            return $genreList;
        }
    }

    /**
     * returns $this->members from parent::__getAllMembers
     *
     * @return arraylist
     *
     */
    public function getMembers()
    {
        return parent::getAllMembers();
    }

}

