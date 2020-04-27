#!/usr/bin/env php
<?php

/**
 * WIP. The spreadsheet is here: https://docs.google.com/spreadsheets/d/1c3yYf0z2sny6MdjCHXPyzI6ihslFep2GZei4RecUdA0/edit#gid=0
 */

include_once('src/QueryBase.php');
include_once('src/Helpers.php');

$dayCounts = \Balsama\Helpers::arrayKeyDates('2020-03-01', '2020-04-25');
$dayTempsCsv = array_map('str_getcsv', file('data/temps/temps.csv'));

$terms = [
    'mask',
    'distancing',
    'social',
    'essential',
    'congregating',
    'distance',
];

foreach ($terms as $term) {
    $days[] = ['dat', 'temp', 'count'];
    foreach ($dayTempsCsv as $dayTemp) {
        $days[$dayTemp[0]] = [
            'day' => $dayTemp[0],
            'temp' => $dayTemp[1],
            'termCount' => 0,
        ];
    }

    $query = new QueryBase('updated-other');
    $query->filterRecordsByFieldContains('description', [$term]);
    $submissionDates = $query->getMatches();

    foreach ($submissionDates as $submissionDate) {
        $submissionDate = (array) $submissionDate;
        if (isset($submissionDate['requested_datetime'])) {
            $ymd = substr($submissionDate['requested_datetime'], 0, 10);
            if (isset($days[$ymd])) {
                $days[$ymd]['termCount']++;
            }
        }
    }
    \Balsama\Helpers::writeToCSVFile("csv/daily-$term-complaints", $days);
}

$foo = 21;