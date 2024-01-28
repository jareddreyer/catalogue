<?php

use App\Catalogue\PageTypes\FilmsPage;
use App\Catalogue\PageTypes\MaintenanceFormPage;
use App\Catalogue\PageTypes\ProfilePage;
use App\Catalogue\PageTypes\TelevisionPage;
use SilverStripe\Assets\Folder;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\DB;

class Page extends SiteTree
{

    /**
     * @inheritDoc
     */
    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();

        if (!MaintenanceFormPage::get()->first()) {
            $maintenanceFormPage = MaintenanceFormPage::create();
            $maintenanceFormPage->Title = 'Insert new media';
            $maintenanceFormPage->Content = '';
            $maintenanceFormPage->write();
            $maintenanceFormPage->publish('Stage', 'Live');
            $maintenanceFormPage->flushCache();
            DB::alteration_message('Catalog form page created', 'created');
        }

        if (!TelevisionPage::get()->first()) {
            $televisionPage = TelevisionPage::create();
            $televisionPage->Title = 'TV Shows';
            $televisionPage->Content = '';
            $televisionPage->write();
            $televisionPage->publish('Stage', 'Live');
            $televisionPage->flushCache();
            DB::alteration_message('TV shows page created', 'created');
        }

        if (!FilmsPage::get()->first()) {
            $filmsPage = FilmsPage::create();
            $filmsPage->Title = 'Movies';
            $filmsPage->Content = '';
            $filmsPage->write();
            $filmsPage->publish('Stage', 'Live');
            $filmsPage->flushCache();
            DB::alteration_message('Movies page created', 'created');
        }

        if (!ProfilePage::get()->first()) {
            $profilePage = ProfilePage::create();
            $profilePage->Title = 'Your Profile';
            $profilePage->Content = '';
            $profilePage->ShowInMenus = false;
            $profilePage->write();
            $profilePage->publish('Stage', 'Live');
            $profilePage->flushCache();
            DB::alteration_message('Catalog profile page created', 'created');
        }

        // delete about us and contact us pages from default install
        if (SiteTree::get_by_link('about-us') || SiteTree::get_by_link('contact-us')) {
            $contactusPage = Page::get()->byID(3);
            $aboutusPage = Page::get()->byID(2);
            $contactusPage->delete();
            $aboutusPage->delete();
            DB::alteration_message("Deleting 'about us' & 'contact us' pages", 'deleted');
        }
    }

}
