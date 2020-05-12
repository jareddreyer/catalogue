<?php

class Catalogue extends DataObject
{
    private static $db = [
        'Title'    => 'Varchar(255)',
        'IMDBID'   => 'Varchar(50)',
        'Type'     => 'Varchar(100)',
        'Year'     => 'Varchar(10)',
        'Genre'    => 'Varchar(100)',
        'Keywords' => 'Varchar(100)',
        'Trilogy'  => 'Varchar(100)',
        'Seasons'  => 'Varchar(200)',
        'Status'   => 'Varchar(100)',
        'Source'   => 'Varchar(50)',
        'Quality'  => 'Varchar(50)',
        'Comments' => 'Text',
    ];

    private static $has_one = [
        'Metadata' => File::class,
        'Poster'   => Image::class,
        'Owner'    => Member::class
    ];

    private static $summary_fields = [
        'ID',
        'Poster.CMSThumbnail' => 'Poster',
        'Title',
        'Type',
        'Year',
        'Genre',
        'LastEdited.Nice' => 'Last edited'
    ];

    private static $searchable_fields = [
        'Title',
        'Type'
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

        if($media = Catalogue::get()
            ->filter(
            [
                'Title' => $this->Title,
                'Year'  => $this->Year,
                'Type'  => $this->Type
            ])
            ->exclude('ID', $this->ID)
            ->first()
        ) {
            $result->error($media->Title . ' (' . $media->Year .') has already been inserted to the catalogue.');
        }

        return $result;
    }
}
