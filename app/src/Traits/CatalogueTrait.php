<?php

namespace App\Catalogue\Traits;

use App\Catalogue\Models\Catalogue;
use PageController;
use SilverStripe\Assets\Folder;
use SilverStripe\Assets\FolderNameFilter;
use SilverStripe\Assets\Image;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Manifest\ModuleResourceLoader;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ValidationException;

trait CatalogueTrait
{

    /**
     * Helper to return a safe file name by removing characters disallowed in filenames.
     *
     * @param string $title - name of media
     */
    public static function getSanitizedFilename(string $title, string $year, string $fileType): string
    {
        $sanitized = preg_replace('/[^a-zA-Z0-9-_.]/', '', $title);
        $fileExtension = $fileType === 'image' ? '.jpg' : '.txt';

        return $sanitized . '-(' . $year . ')' . $fileExtension;
    }

    /**
     * This method we strip out any special characters and dash everything else.
     * This is only used for names of folders and URL purposes.
     *
     * Silverstripe {@see FolderNameFilter::$default_replacements} itself allows for
     * characters that should not be in folder names.
     */
    public static function getSanitizedTitle(string $title): string
    {
        // Create a filter object and assign our default matching patterns.
        $filter = FolderNameFilter::create();

        $defaultReplacements = [
            '/[^a-zA-Z0-9\-]/' => '-',
            '/-{2,}/' => '-',
        ];

        $filter->setReplacements($defaultReplacements);

        // Return a Title that is safe for both Folder names and File names.
        return trim($filter->filter($title), '-');
    }

    /**
     * @param string $name - Name of folder this should be sanitized and SEO friendly
     * @param string $title - title of folder this should be sanitized and is CMS/User friendly.
     * @throws ValidationException
     */
    public static function findOrCreateAssetFolders(string $name, string $title): Folder
    {
        // Find our catalogue root folder
        $catalogueFolder = Folder::find_or_make(Catalogue::singleton()->catalogueFolderName);

        // Find this Catalogue folder item from its title
        $titleFolder = Folder::get()->filter(['Name' => $name])->first();

        // Check our find did not get a folder for this catalogue item
        if (is_null($titleFolder)) {
            // Folder did not exist, we are creating it.
            $titleFolder = Folder::find_or_make($name);
            $titleFolder->update([
                'ParentID' => $catalogueFolder->ID,
                'FolderTitle' => $title,
            ]);
            $titleFolder->write();
        }

        // Return the catalogue title folder for further use.
        return $titleFolder;
    }

    /**
     * Build a request for our fetch because we may not have a metadata file,
     * and we need an imdb ID for our identifier on our files and since,
     * we only have identifier when we have a new title added to the catalogue or,
     * the user has fetched a profile of the title.
     *
     * @param string[] $requestData
     */
    public static function buildPosterRequest(array $requestData): HTTPRequest
    {
        $pageController = PageController::singleton();
        $link = $pageController::join_links($pageController->getMaintenanceFormPageLink(), 'Poster');

        return new HTTPRequest(
            'GET',
            $link,
            $requestData
        );
    }

    /**
     * Returns poster image object for the template view by ID.
     */
    public function getPosterImageByID($id = null): DataObject|string|null
    {
        if (!$id || !Image::get_by_id($id)) {
            // Found nothing so returning blank.
            return ModuleResourceLoader::resourceURL('themes/app/images/blank.png');
        }

        return Image::get_by_id($id);
    }

}
