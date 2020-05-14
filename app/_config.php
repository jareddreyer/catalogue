<?php

global $project;
$project = 'catalog';

global $databaseConfig;
require_once('conf/ConfigureFromEnv.php');

// @todo this will be called eventually by environment::getVariables() in SS4, for SS3 it needs to be this way.
global $omdbAPIKey;
$omdbAPIKey = omdbAPIKey;

//set paths for Posters and Metadata for both TV & Film
DataObject::add_extension('SiteConfig', 'SiteConfigExtension');

//logging
SS_Log::add_writer(new SS_LogFileWriter('../logs/silverstripe-errors-warnings.log'), SS_Log::WARN, '<=');

// Set the site locale
i18n::set_locale('en_US');
