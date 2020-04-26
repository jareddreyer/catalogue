<?php

class CatalogueAdmin extends ModelAdmin
{
    private static $managed_models = [
        'Catalogue'
    ];

    private static $menu_title = 'Catalogue admin';

    private static $model_importers = [
        'Catalogue' => 'CatalogueCsvBulkLoader',
    ];

    private static $url_segment = 'catalogue';
}
