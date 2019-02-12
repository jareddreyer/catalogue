<?php

class SiteConfigExtension extends DataExtension
{
	private static $db = [
		'PostersUploadFolder' 	=> 'Text',
		'PostersWebpath' 		=> 'Text',
		'JSONUploadFolder' 		=> 'Text',
		'OMDBAPIKey'			=> 'Varchar'
	];

	public function updateCMSFields(FieldList $fields)
	{

		$fields->addFieldToTab("Root.Catalogue", TextField::create('PostersUploadFolder', 'Poster Upload Directory')->setDescription(ASSETS_PATH.'/Posters/'));
		$fields->addFieldToTab("Root.Catalogue", TextField::create('PostersWebpath', 'Posters Directory Web Path')->setDescription('/assets/Posters/'));
		$fields->addFieldToTab("Root.Catalogue", TextField::create('JSONUploadFolder', 'Name')->setDescription(ASSETS_PATH.'/Metadata/'));
		$fields->addFieldToTab("Root.Catalogue", TextField::create('OMDBAPIKey', 'OMDB API Key')->setDescription('Set key here'));

	}
}