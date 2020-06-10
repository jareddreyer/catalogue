<?php

class Catalogue extends DataObject
{
    private static $db = [
        'Title'      => 'Varchar(255)',
        'IMDBID'     => 'Varchar(20)',
        'Type'       => 'Varchar(10)',
        'Year'       => 'Varchar(4)',
        'Genre'      => 'Varchar(255)',
        'Keywords'   => 'Varchar(255)',
        'Collection' => 'Varchar(100)',
        'Seasons'    => 'Text',
        'Status'     => 'Varchar(50)',
        'Source'     => 'Varchar(10)',
        'Quality'    => 'Varchar(5)'
    ];

    private static $has_one = [
        'Metadata' => File::class,
        'Poster'   => Image::class,
        'Owner'    => Member::class
    ];

    private static $has_many = [
        'Comments' => Comment::class
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

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $comments = $fields->dataFieldByName('Comment');

        if($comments) {
            $comments->getConfig()->addComponent(new GridFieldDeleteAction());
        }

        return $fields;

    }

}
