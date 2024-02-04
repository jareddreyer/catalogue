<?php

namespace App\Catalogue\Models;

use App\Catalogue\Traits\CatalogueTrait;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\ValidationException;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Member;
use stdClass;

class Catalogue extends DataObject
{

    use CatalogueTrait;

    private static string $table_name = 'Catalogue';

    private static string $singular_name = 'Catalogue Item';

    private static string $plural_name = 'Catalogue Items';

    private static string $description = 'Catalogue of media titles';

    private static array $db = [
        'Title' => 'Varchar(255)',
        'ImdbID' => 'Varchar(20)',
        'Type' => 'Varchar(10)',
        'Year' => 'Varchar(4)',
        'Genre' => 'Varchar(255)',
        'Keywords' => 'Varchar(255)',
        'Collection' => 'Varchar(100)',
        'Seasons' => 'Text',
        'Status' => 'Varchar(50)',
        'Source' => 'Varchar(10)',
        'Quality' => 'Varchar(5)',
        'LastFetched' => 'DBDatetime',
        'PosterURL' => 'Text',
        'MarkAsIncomplete' => 'Boolean'
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

    private static array $defaults = [
        'MarkAsIncomplete' => true,
    ];

    public string $catalogueFolderName = 'catalogue-metadata';

    /**
     * @todo needs tidying up.
     */
    public function validate(): ValidationResult
    {
        $result = parent::validate();

        if ($media = Catalogue::get()
            ->filter(
            [
                'Title' => $this->Title,
                'Year'  => $this->Year,
                'Type'  => $this->Type
            ])
            ->exclude('ID', $this->ID)
            ->first()
        ) {
            $result->addError($media->Title . ' (' . $media->Year .') has already been inserted to the catalogue.');
        }

        return $result;
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        // Remove delete action from the gridfield.
        // todo why the heck did I add this??
        $comments = $fields->dataFieldByName('Comment');
        $comments?->getConfig()->addComponent(new GridFieldDeleteAction());

        return $fields;
    }

    /**
     * Take an OMDB Api response and hydrate the catalogue DBO
     * with its results.
     * @throws ValidationException
     * @todo check folder of title exists by ID rather than path, so we can handle tv shows that have not finished e.g. tv show (2024 -)
     */
    public function hydrateMetadataFromResponse(stdClass $data): void
    {
        // First sanitize the tile in case it has characters not allowed in filenames.
        $cleanedMetadataFolderTitle = self::getSanitizedTitle($data->Title);
        $metadataAssetParts = [
            'filename' => $cleanedMetadataFolderTitle . '.txt',
            'file_title' => $data->Title . ' (' . $data->Year . ')',
            'folder_name' => $cleanedMetadataFolderTitle . '-' . $data->imdbID,
            'folder_title' => $data->Title . ' (' . $data->Year . ')',
        ];

        // Get our folder for this title.
        $titleFolder = self::findOrCreateAssetFolders(
            $metadataAssetParts['folder_name'],
            $metadataAssetParts['folder_title'],
        );

        // Set the filename for this metadata file
        $metadataFilename = [
            $titleFolder->getFilename(),
            $metadataAssetParts['filename'],
        ];

        // Create the metadata name e.g. '2-Fast-2-Furious-2003/2-Fast-2-Furious(2003).txt'
        $metadataName = File::join_paths($metadataFilename);

        // Get or Create Metadata relations and files.
        $file = $this->findOrCreateCatalogueAsset('Metadata');
        $file->setFromString(json_encode($data), $metadataName);
        $file->update([
            'Title' => $metadataAssetParts['file_title'],
            'ParentID' => $titleFolder->ID,
        ]);
        $file->write();
        $file->publishSingle();

        // Finally with our response of our metadata, hydrate our Catalogue record
        // NB: Assume the omdb api data is the single source of truth over what we have stored in DB.
        $this->update([
            'Title' => $data->Title,
            'ImdbID' => $data->imdbID,
            'Year' => $data->Year,
            'Genre' => $data->Genre,
            'MetadataID' => $file->ID,
            'LastFetched' => DBDatetime::now()->Format(DBDatetime::ISO_DATETIME),
            'PosterURL' => $data->Poster,
            'MarkAsIncomplete' => false,
        ])->write();
    }

    /**
     * @throws ValidationException
     */
    public function hydratePosterFromResponse(stdClass $data, string $posterSource): void
    {
        // First sanitize the tile in case it has characters not allowed in filenames.
        $cleanedMetadataFolderTitle = self::getSanitizedTitle($data->Title);
        $metadataAssetParts = [
            'filename' => $cleanedMetadataFolderTitle . '-' . $data->Year . '.jpg',
            'title' => $data->Title . ' (' . $data->Year . ')',
            'folder_name' => $this->catalogueFolderName . '/' .$cleanedMetadataFolderTitle . '-' . $data->imdbID,
        ];

        // Get our folder for this title.
        $titleFolder = self::findOrCreateAssetFolders(
            $metadataAssetParts['folder_name'],
            $metadataAssetParts['title'],
        );

        // Set the filename for this poster file
        $metadataFilename = [
            $titleFolder->getFilename(),
            $metadataAssetParts['filename'],
        ];

        // Create the poster name e.g. '2Fast2Furious_2003/2Fast2Furious-2003.jpg'
        $metadataName = File::join_paths($metadataFilename);

        // Get or Create Poster relations and files.
        $file = $this->findOrCreateCatalogueAsset('Poster');
        $file->setFromString($posterSource, $metadataName);
        $file->update([
            'Name' => $metadataName,
            'Title' => $metadataAssetParts['title'],
            'ParentID' => $titleFolder->ID,
        ]);
        $file->write();
        $file->publishSingle();

        // Finally with our response of our Poster image, hydrate our Catalogue record
        $this->update([
            'PosterID' => $file->ID,
        ])->write();
    }

    /**
     *  Find or create poster|metadata DataObject
     */
    public function findOrCreateCatalogueAsset(string $type): File|Image
    {
        $relation = $this->{$type}();

        if ($relation->exists()) {
            return $relation;
        }

        // Relation of the specified type not found, just return a new DBO.
        return match ($type) {
            'Metadata' => File::create(),
            'Poster' => Image::create(),
        };
    }

}
