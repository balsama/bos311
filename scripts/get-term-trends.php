#!/usr/bin/env php
<?php

/**
 * WIP. The spreadsheet is here: https://docs.google.com/spreadsheets/d/1YOnb7nKvXv_GsrAZrAsbOsEEFYlSfVzJw5FbJICSuvo/edit#gid=0 
 */

include_once('src/QueryBase.php');
include_once('src/Helpers.php');

$monthCounts = [
    '2015-01' => 0,
    '2015-02' => 0,
    '2015-03' => 0,
    '2015-04' => 0,
    '2015-05' => 0,
    '2015-06' => 0,
    '2015-07' => 0,
    '2015-08' => 0,
    '2015-09' => 0,
    '2015-10' => 0,
    '2015-11' => 0,
    '2015-12' => 0,
    '2016-01' => 0,
    '2016-02' => 0,
    '2016-03' => 0,
    '2016-04' => 0,
    '2016-05' => 0,
    '2016-06' => 0,
    '2016-07' => 0,
    '2016-08' => 0,
    '2016-09' => 0,
    '2016-10' => 0,
    '2016-11' => 0,
    '2016-12' => 0,
    '2017-01' => 0,
    '2017-02' => 0,
    '2017-03' => 0,
    '2017-04' => 0,
    '2017-05' => 0,
    '2017-06' => 0,
    '2017-07' => 0,
    '2017-08' => 0,
    '2017-09' => 0,
    '2017-10' => 0,
    '2017-11' => 0,
    '2017-12' => 0,
    '2018-01' => 0,
    '2018-02' => 0,
    '2018-03' => 0,
    '2018-04' => 0,
    '2018-05' => 0,
    '2018-06' => 0,
    '2018-07' => 0,
    '2018-08' => 0,
    '2018-09' => 0,
    '2018-10' => 0,
    '2018-11' => 0,
    '2018-12' => 0,
    '2019-01' => 0,
    '2019-02' => 0,
    '2019-03' => 0,
    '2019-04' => 0,
    '2019-05' => 0,
    '2019-06' => 0,
    '2019-07' => 0,
    '2019-08' => 0,
    '2019-09' => 0,
    '2019-10' => 0,
    '2019-11' => 0,
    '2019-12' => 0,
    '2020-01' => 0,
    '2020-02' => 0,
    '2020-03' => 0,
    '2020-04' => 0,
];

$terms = [
    '!!!',
    '!!!!',
    'gathering',
    'drinking',
    'homeless',
    'mask',
    'home',
    'stay',
    'fuck',
    'mayor',
    'demand',
    'bird',
    'dog',
    'distancing',
];

$termMonthCounts = [];
foreach ($terms as $term) {

    $termMonthCounts[$term] = $monthCounts;

    $query = new QueryBase('updated-other');
    $query->filterRecordsByFieldContains('description', [$term]);
    $submissionDates = $query->getMatches(['requested_datetime']);

    foreach ($submissionDates as $submissionDate) {
        $ym = substr($submissionDate, 0, 7);
        if (isset($termMonthCounts[$term][$ym])) {
            $termMonthCounts[$term][$ym]++;
        }
    }
    \Balsama\Helpers::writeToCSVFile("csv/$term-complaints", $termMonthCounts[$term]);

}

$foo = 21;