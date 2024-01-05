<?php

namespace App\Catalogue\Admin;

use App\Catalogue\Extensions\CatalogueCsvBulkLoader;
use App\Catalogue\Models\Catalogue;
use SilverStripe\Admin\ModelAdmin;

class CatalogueAdmin extends ModelAdmin
{

    private static array $managed_models = [
        Catalogue::class,
    ];

    private static string $menu_title = 'Catalogue admin';

    private static array $model_importers = [
        Catalogue::class => CatalogueCsvBulkLoader::class,
    ];

    private static string $url_segment = 'catalogue';

}
