<?php

namespace App\Catalogue\Extensions;


use SilverStripe\AssetAdmin\Controller\AssetAdmin;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;

class FolderFormFactory extends DataExtension
{
    public function updateFormFields(FieldList $fields, ?AssetAdmin $controller, ?string $formName, ?array $context)
    {
        // Get data object from the context parameter
        $folder = $context['Record'] ?? null;

        $fields->insertAfter('Name', TextField::create('FolderTitle', 'Catalogue item folder Title')
            ->setDescription('Because the "Name" field is used for SEO we need the actual value for a "title" field 
                to be used in the CMS.'));

        // Ensure name field is readonly as they are populated from the metadata request and shouldn't change.
        if ($folder !== null) {
            $fields->replaceField('Name', $fields->dataFieldByName('Name')->performReadonlyTransformation());
        }
    }

}
