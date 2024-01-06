<?php

namespace App\Catalogue\Models;

use SilverStripe\Forms\RequiredFields;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;

class Comment extends DataObject
{

    private static $singular_name = 'Catalogue user comment';
    private static $plural_name = 'Catalogue user comments';
    private static $table_name = 'Comment';

    private static $db = [
        'Comment' => 'Text',
    ];

    private static $has_one = [
        'Author' => Member::class,
        'Catalogue' => Catalogue::class,
    ];

    private static $summary_fields = [
        'AuthorName' => 'Author',
        'Created.Ago' => 'Created',
    ];

    public function canDelete($member = null)
    {
        return Permission::check('ADMIN');
    }

    public function canCreate($member = null, $context = [])
    {
        return Permission::check('ADMIN');
    }

    public function canView($member = null)
    {
        return true;
    }

    public function canEdit($member = null)
    {
        return Permission::check('CMS_ACCESS_TranslationAdmin');
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('CatalogueID');
        $fields->dataFieldByName('AuthorID')->setCustomValidationMessage('Author must be set when saving a comment.');

        return $fields;
    }

    public function getAuthorName()
    {
        if ($this->Author && $this->Author->exists()) {
            return $this->Author->FirstName . ' ' .$this->Author->Surname;
        }

        $member = DataObject::get_by_id(Member::class, Member::currentUserID());

        return $member->FirstName .' '. $member->Surname;
    }

    public function getCMSValidator()
    {
        return new RequiredFields(['AuthorID']);
    }

}
