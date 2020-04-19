<?php

class Catalogue extends DataObject
{
    private static $db = [
        'VideoTitle' => 'Varchar(255)',
        'IMDBID'     => 'Varchar(50)',
        'VideoType'  => 'Varchar(100)',
        'Year'       => 'Varchar(10)',
        'Genre'      => 'Varchar(100)',
        'Keywords'   => 'Varchar(100)',
        'Trilogy'    => 'Varchar(100)',
        'Seasons'    => 'Varchar(200)',
        'Status'     => 'Varchar(100)',
        'Source'     => 'Varchar(50)',
        'Quality'    => 'Varchar(50)',
        'Comments'   => 'Text',
    ];

    private static $has_one = [
        'Poster' => Image::class,
        'Owner'  => Member::class
    ];

    private static $summary_fields = [
        'ID',
        'VideoTitle' => 'Title',
        'VideoType' => 'Type',
        'Year',
        'Genre',
        'IMDBID' => 'IMDB ID',
        'LastEdited.Nice' => 'Last edited'
    ];

    private static $searchable_fields = [
        'VideoTitle',
        'VideoType'
    ];

    public function canView($member = null) {
        return Permission::check('CMS_ACCESS_MyAdmin', 'any', $member);
    }

    public function canEdit($member = null) {
        return Permission::check('CMS_ACCESS_MyAdmin', 'any', $member);
    }

    public function canDelete($member = null) {
        return Permission::check('CMS_ACCESS_MyAdmin', 'any', $member);
    }

    public function canCreate($member = null) {
        return Permission::check('CMS_ACCESS_MyAdmin', 'any', $member);
    }

    public function validate()
    {
        $result = parent::validate();

        if(DataObject::get_one('Catalogue',
            [
                'VideoTitle' => $this->VideoTitle,
                'Year' => $this->Year
        ]
        )) {
            $result->error('This media has already been inserted to the catalogue.');
        }

        return $result;
    }

}
