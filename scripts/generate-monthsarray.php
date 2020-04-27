#!/usr/bin/env php
<?php
include_once('src/QueryBase.php');
include_once('src/Helpers.php');

$query = new QueryBase('updated-other');
$query->filterRecordsByFieldContains('description', ['home']);
$submissionDates = $query->getMatches(['requested_datetime']);

$monthCounts = [];
foreach ($submissionDates as $submissionDate) {
    $ym = substr($submissionDate, 0, 7);
    $monthCounts[$ym] = 0;
}
ksort($monthCounts);

foreach ($monthCounts as $month => $monthCount) {
    echo "'$month' => 0,\n";
}


\Balsama\Helpers::writeToCSVFile('csv/months-complaints', $monthCounts);
