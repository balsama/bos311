#!/usr/bin/env php
<?php

$endpoint = 'https://mayors24.cityofboston.gov/open311/v2/requests.json?service_code=4f38920fe75084437f00018d&jurisdiction_id=boston.gov&page=';
$filename = 'responses--sidewalk-not-shovelled--missing-407-412.json';
$i = 407;

while ($i < 413) {
    $contents = file_get_contents($endpoint . $i);
    file_put_contents($filename, $contents, FILE_APPEND);
    echo 'Done with request number ' . $i . "\n";
    if ($i % 10 == 0) {
        fixJson($filename);
    }
    $i++;
}
fixJson($filename);

function fixJson($filename) {
    $contents = '[' . file_get_contents($filename) . ']';
    $contents = str_replace('[[', '[', $contents);
    $contents = str_replace("},\n]", '}]', $contents);
    $contents = str_replace('],[', ',', $contents);
    $contents = str_replace('][', '', $contents);
    $contents = str_replace('}{', '},{', $contents);
    $contents = str_replace(']]', ']', $contents);
    $contents = str_replace('][', ',', $contents);
    $contents = str_replace('},{', "},\n{", $contents);

    file_put_contents($filename, $contents);
}