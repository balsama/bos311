#!/usr/bin/env php
<?php
include_once('src/QueryBase.php');
include_once('src/Helpers.php');

$query = new QueryBase('updated-other');
$query->filterRecordsByFieldContains('description', ['dog', 'leash']);
$submissionDates = $query->getMatches(['requested_datetime']);

$monthCounts = [];
foreach ($submissionDates as $submissionDate) {
    $foo = 21;
    $ym = substr($submissionDate, 0, 7);
    if (isset($monthCounts[$ym])) {
        $monthCounts[$ym]++;
    }
    else {
        $monthCounts[$ym] = 1;
    }
}
ksort($monthCounts);

\Balsama\Helpers::writeToCSVFile('csv/dog-leash-complaints', $monthCounts);
