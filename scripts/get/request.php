#!/usr/bin/env php
<?php

$endpoint = 'https://mayors24.cityofboston.gov/open311/v2/requests.json?service_code=4f389210e75084437f0001e5&jurisdiction_id=boston.gov&page=';
$filename = 'responses--1999+.json';
$i = 1999;

while ($i < 2700) {
    $contents = file_get_contents($endpoint . $i);
    file_put_contents($filename, $contents, FILE_APPEND);
    echo 'Done with request number ' . $i . "\n";
    if ($i % 10 == 0) {
        clean($filename);
    }
    $i++;
}
