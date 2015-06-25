<?php    
class Catalogue extends DataObject {
    private static $db = array(
        'Video_title' => 'VARCHAR(300)',
		'Video_type' => 'VARCHAR(100)',
		'Genre' => 'VARCHAR(100)',
		'Seasons' => 'VARCHAR(200)',		
		'Status' => 'VARCHAR(100)',
		'Source' => 'VARCHAR(50)',
		'Quality' => 'VARCHAR(50)',
		'Owner' => 'VARCHAR(100)',
		'Comments' => 'TEXT',
		'Wanted_by' => 'VARCHAR(200)',
		'Last_updated' => "VARCHAR(100)"
    );
    
}