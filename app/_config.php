<?php

global $project;
$project = 'Catalogue';

global $databaseConfig;
require_once('conf/ConfigureFromEnv.php');

//set paths for Posters and Metadata for both TV & Film
DataObject::add_extension('SiteConfig', 'SiteConfigExtension');

//logging
SS_Log::add_writer(new SS_LogFileWriter('../logs/silverstripe-errors-warnings.log'), SS_Log::WARN, '<=');

// Set the site locale
i18n::set_locale('en_US');

//set local timezone (if not in php.ini)
date_default_timezone_set('Pacific/Auckland');
