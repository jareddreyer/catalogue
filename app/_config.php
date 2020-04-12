<?php

global $project;
$project = 'catalog';

global $databaseConfig;
require_once('conf/ConfigureFromEnv.php');

//set paths for Posters and Metadata for both TV & Film
DataObject::add_extension('SiteConfig', 'SiteConfigExtension');
define('APIKEY', 'a0f02af4');

//logging
SS_Log::add_writer(new SS_LogFileWriter('../logs/silverstripe-errors-warnings.log'), SS_Log::WARN, '<=');

// Set the site locale
i18n::set_locale('en_US');
