<?php

namespace App\Catalogue\Admin;

use App\Catalogue\Extensions\CatalogueCsvBulkLoader;
use App\Catalogue\Models\Catalogue;
use SilverStripe\Admin\ModelAdmin;

class CatalogueAdmin extends ModelAdmin
{

    private static array $managed_models = [
        'catalogue-complete' => [
            'dataClass' => Catalogue::class,
            'title' => 'Catalogue',
        ],
        'catalogue-incomplete' => [
            'dataClass' => Catalogue::class,
            'title' => 'Incomplete records',
        ],
    ];

    private static string $menu_title = 'Catalogue admin';

    private static array $model_importers = [
        Catalogue::class => CatalogueCsvBulkLoader::class,
    ];

    private static string $url_segment = 'catalogue';

    public function getList()
    {
        $list = parent::getList();

        // Only show Catalogue items that are marked as incomplete in the 'Incomplete records' tab
        if ($this->modelTab === 'catalogue-incomplete') {
            $list = $list->filter('MarkAsIncomplete', true);
        }

        // Only show Catalogue items that are NOT marked as incomplete in the 'Catalogue' tab
        if ($this->modelTab === 'catalogue-complete') {
            $list = $list->filter('MarkAsIncomplete', false);
        }

        return $list;
    }

}
