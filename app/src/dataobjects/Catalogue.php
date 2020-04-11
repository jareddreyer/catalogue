<?php
class Catalogue extends DataObject
{
    private static $db = array(
        'Video_title' => 'VARCHAR(300)',
        'imdbID'      => 'VARCHAR(50)',
        'Video_type'  => 'VARCHAR(100)',
        'Year'        => 'VARCHAR(10)',
        'Genre'       => 'VARCHAR(100)',
        'Keywords'    => 'VARCHAR(100)',
        'Trilogy'     => 'VARCHAR(100)',
        'Seasons'     => 'VARCHAR(200)',
        'Status'      => 'VARCHAR(100)',
        'Source'      => 'VARCHAR(50)',
        'Quality'     => 'VARCHAR(50)',
        'Owner'       => 'INT',
        'Comments'    => 'TEXT',
        'Poster'      => 'varchar(100)'
    );

    public function validate()
    {
        $result = parent::validate();

        if(Catalogue::get()->filter(array('Video_title' => $this->Video_Title))->first())
        {
            $result->error('This media has already been inserted to the catalogue.');
        }

        return $result;
    }

}
