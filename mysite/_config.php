<?php

global $project;
$project = 'mysite';

global $databaseConfig;
$databaseConfig = array(
	"type" => 'MySQLDatabase',
	"server" => 'localhost',
	"username" => 'root',
	"password" => '12345',
	"database" => 'catalogue',
	"path" => '',
);
SS_Log::add_writer(new SS_LogFileWriter('c:/inetpub/catalogue/logs/'), SS_Log::WARN, '<=');
// Set the site locale
i18n::set_locale('en_US');

ini_set("log_errors", "On");
//ini_set("error_log", "c:/inetpub/catalogue/logs/");