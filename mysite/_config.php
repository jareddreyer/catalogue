<?php

global $project;
$project = 'mysite';

global $databaseConfig;
require_once('conf/ConfigureFromEnv.php');

//set paths for Posters and Metadata for both TV & Film
define('POSTERSDIR', ASSETS_PATH.'/Posters/');
define('POSTERSWEBPATH', '/assets/Posters/');
define('JSONDIR', ASSETS_PATH.'/Metadata/');
define('APIKEY', 'a0f02af4');

//logging
SS_Log::add_writer(new SS_LogFileWriter($_SERVER['DOCUMENT_ROOT'].'/../logs/silverstripe-errors-warnings.log'), SS_Log::WARN, '<=');

// Set the site locale
i18n::set_locale('en_US');