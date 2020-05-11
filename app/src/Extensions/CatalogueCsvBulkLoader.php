<?php

/**
 * Class CatalogueCsvBulkLoader
 *
 * NOTE:
 * Because the CSVBulkLoader class does not cater and allow multi-column conditional checking,
 * I had to remove duplicateChecks static. This could be enabled but then any movie with a same name
 * (but different release year) would be treated as a duplicate and dropped from the import,
 * regardless if the year value is different.
 *
 * e.g. Title => 'The Lion King'
 *      Year  => '1994'
 *      Title => 'The Lion King'
 *      Year  => '2019'
 *
 * Because this is disabled, the import function will ungracefully error crash with a dataObject::validate() error
 * of finding any duplicates being inserted into the catalogue.
 *
 * In this case it is better to remove any duplicates before trying to import the csv file and add it in manually
 * via the main insert media controller and avoid this error.
 *
 * NOTE (2):
 * This was the less evil approach for two reasons:
 * 1. duplicateChecks static does not provide the same user_error logging
 *    as DataObject::validate() does (dupicateChecks is silent).
 * 2. Treating similar Title names with different release year as duplicates and dropping these records from the import
 *    would require more manual CSV clean up work for the user and more additional work to manually insert them.
 *
 * @todo refactor when SS allows for multi-column condition checking.
 *
 * @see CsvBulkLoader
 */
class CatalogueCsvBulkLoader extends CsvBulkLoader {

    public $columnMap = [
        'Title'   => '->importTitle',
        'Year'    => '->importYear',
        'Source'  => '->importSource',
        'Seasons' => '->importSeasons',
        'Quality' => 'Quality',
        'Type'    => 'Type',
        'Status'  => 'Status',
        'OwnerID' => 'OwnerID'
    ];

    /**
     * Cleans up titles ready to be ran against the omdb API
     *
     * @param $obj
     * @param $val
     * @param $record
     */
    public static function importTitle(&$obj, $val, $record)
    {
        $re = '/^(.+?)[.( \t]*(?:(?:(19\d{2}|20(?:0\d|1[0-9]))).*|(?:(?=bluray|\d+p|brrip|LINE|R5|WEBRip)..*)?[.](mkv|rets|xvid|avi|mpe?g|mp4)$)/mi';
        $title = preg_replace($re, '$1', $val); // bind the title for next run

        // now run a second pass over the title because movie titles are the dirtiest strings in the world!
        if(strpos($title, ".") !== false ) {
            $title = preg_split('/[.]/', $title);
            $title = array_filter($title, function(&$var) { return !(preg_match("/(?:HDTV|HC|DTS|YIFY|WEB-DL|R5|H264|DD5|HDRip|XVid|bluray|\w{2,3}rip)|(?:x264)|(?:\d{4})|(?:\d{3,4}p)|(?:AC\d)/i", $var) ); } );
            $title = join(' ', $title);
        }

        // remove any underscores thats not captured and removed already
        $title = str_replace("_", " ", $title);
        $title = str_replace("[", "", $title);

        // Finally title case everything so it looks clean.
        $obj->Title = ucwords($title);
    }

    public static function importSource(&$obj, $val, $record)
    {
        switch ($val)
        {
            case 'Matroska / WebM':
                $obj->Source = 'WebM';
                break;
            case ('MPEG-TS (MPEG-2 Program Stream)' && $record['Quality'] == '480p'):
            case ('AVI (Audio Video Interleaved)' && $record['Quality'] == '480p'):
            case ('QuickTime / MOV' && $record['Quality'] == '480p'):
                $obj->Source = 'DVD';
                break;
            case ('MPEG-TS (MPEG-2 Transport Stream)' && $record['Quality'] == '1080p'):
            case ('AVI (Audio Video Interleaved)' && $record['Quality'] == '1080p'):
            case ('AVI (Audio Video Interleaved)' && $record['Quality'] == '720p'):
            case ('QuickTime / MOV' && $record['Quality'] == '720p'):
            case ('QuickTime / MOV' && $record['Quality'] == '1080p'):
                $obj->Source = 'Bluray';
                break;
            default:
                $obj->Source = 'Bluray';
        }
    }

    public static function importYear(&$obj, $val, $record)
    {
        // column has a value so use it
        $obj->Year = $val;

        // column does not have value so set it to whatever is in the Title
        if(!empty($val) || $val == null || $val == 'null' || $val == '') {
            $re = '/^[^\d]*(\d{4}).*$/';

            if( preg_match($re, $record['Title'], $matches) )
            {
                $obj->Year = $matches[1];
            } else {
                $obj->Year = $val;
            }
        }
    }

    public static function importSeasons(&$obj, $val, $record)
    {
        if(is_numeric($val)) {
            $seasonsTmpArr = range(1, $val + 1);

            $seasonsArray = [];
            foreach($seasonsTmpArr as $seasonKey => $seasonValue ){
               $seasonsArray[] = 'Season ' . $seasonKey;
            }

            array_shift($seasonsArray);
            $obj->Seasons = implode(',', $seasonsArray);
        } else {
            $obj->Seasons = $val;
        }
    }
}
