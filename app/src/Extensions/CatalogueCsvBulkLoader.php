<?php

class CatalogueCsvBulkLoader extends CsvBulkLoader {

    public $columnMap = [
        'Title' => 'VideoTitle',
        'Name' => '->importFirstAndLastName',
        'Birthday' => 'Birthday',
        'Team' => 'Team.Title',
    ];

    public $duplicateChecks = [
        'Title' => 'VideoTitle'
    ];

    public static function importFirstAndLastName(&$obj, $val, $record) {
        $parts = explode(' ', $val);
        if(count($parts) != 2) return false;
        $obj->FirstName = $parts[0];
        $obj->LastName = $parts[1];
    }

}
