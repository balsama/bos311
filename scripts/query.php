#!/usr/bin/env php
<?php
include_once('src/QueryBase.php');

$query = new QueryBase('sidewalk-not-shovelled');

$query->filterRecordsByFieldContains('status', ['closed']);

function sortByArrayKey($keyA = 'open_date', $keyB = 'open_date')
{
    function cmp($a, $b)
    {
        return strcmp($a['open_date'], $b['open_date']);
    }
}



/**
 * @param $fileName str
 * @param $records array
 */
function writeToFile($fileName, $records)
{
    $fp = fopen($fileName . '.csv', 'w');
    foreach ($records as $record) {
        fputcsv($fp, $record);
    }
    fclose($fp);
}
