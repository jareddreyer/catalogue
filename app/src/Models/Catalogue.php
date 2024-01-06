<?php

namespace App\Catalogue\Models;

use SilverStripe\Assets\File;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

class Catalogue extends DataObject
{

    private static string $table_name = 'Catalogue';

    private static string $singular_name = 'Catalogue Title Item';

    private static string $plural_name = 'Catalogue Title Items';

    private static string $description = 'Catalogue of media titles';

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

    private static array $has_one = [
        'Metadata' => File::class,
        'Poster'   => Image::class,
        'Owner'    => Member::class
    ];

    private static array $has_many = [
        'Comments' => Comment::class
    ];

    private static array $summary_fields = [
        'ID',
        'Poster.CMSThumbnail' => 'Poster',
        'Title',
        'Type',
        'Year',
        'Genre',
        'LastEdited.Nice' => 'Last edited'
    ];

    private static array $searchable_fields = [
        'Title',
        'Type'
    ];

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

        $comments?->getConfig()->addComponent(new GridFieldDeleteAction());

        return $fields;
    }

}
