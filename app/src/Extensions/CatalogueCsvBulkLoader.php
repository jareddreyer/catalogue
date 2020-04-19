<?php

class CatalogueCsvBulkLoader extends CsvBulkLoader {

    public $columnMap = [
        'Number' => 'PlayerNumber',
        'Name' => '->importFirstAndLastName',
        'Birthday' => 'Birthday',
        'Team' => 'Team.Title',
    ];
    public $duplicateChecks = array(
        'Number' => 'PlayerNumber'
    );

    public static function importFirstAndLastName(&$obj, $val, $record) {
        $parts = explode(' ', $val);
        if(count($parts) != 2) return false;
        $obj->FirstName = $parts[0];
        $obj->LastName = $parts[1];
    }

}
