<?php

global $project;
$project = 'mysite';

global $databaseConfig;
require_once('conf/ConfigureFromEnv.php');

//uploads folder location
define('POSTERSDIR', 'c:\inetpub\catalogue\assets\Uploads\\');
define('JSONDIR', 'c:\inetpub\catalogue\assets\Uploads\metadata\\');

// Set the site locale
i18n::set_locale('en_US');