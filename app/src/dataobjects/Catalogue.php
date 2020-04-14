<?php
class Catalogue extends DataObject
{
    private static $db = [
        'VideoTitle' => 'VARCHAR(300)',
        'IMDBID'     => 'VARCHAR(50)',
        'VideoType'  => 'VARCHAR(100)',
        'Year'       => 'VARCHAR(10)',
        'Genre'      => 'VARCHAR(100)',
        'Keywords'   => 'VARCHAR(100)',
        'Trilogy'    => 'VARCHAR(100)',
        'Seasons'    => 'VARCHAR(200)',
        'Status'     => 'VARCHAR(100)',
        'Source'     => 'VARCHAR(50)',
        'Quality'    => 'VARCHAR(50)',
        'Owner'      => 'INT',
        'Comments'   => 'TEXT',
        'PosterID'   => 'varchar(100)'
    ];

    public function validate()
    {
        $result = parent::validate();

        if(DataObject::get_one('Catalogue', ['VideoTitle' => $this->VideoTitle]) )
        {
            $result->error('This media has already been inserted to the catalogue.');
        }

        return $result;
    }

}
