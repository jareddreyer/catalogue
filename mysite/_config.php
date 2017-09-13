<?php

global $project;
$project = 'mysite';

global $databaseConfig;
require_once('conf/ConfigureFromEnv.php');

//set paths (NB: DO NOT ADD STARTING SLASH)
define('SYSTEMROOT', '/var/www/catalogue/');
define('POSTERSDIR', SYSTEMROOT.'assets/Uploads/');
define('JSONDIR', SYSTEMROOT.'assets/Uploads/metadata/');

//logging
SS_Log::add_writer(new SS_LogFileWriter(SYSTEMROOT.'logs/silverstripe-errors-warnings.log'), SS_Log::WARN, '<=');

// Set the site locale
i18n::set_locale('en_US');