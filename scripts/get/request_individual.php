#!/usr/bin/env php
<?php

include_once 'src/QueryBase.php';

$serviceCodes = [
    'New Tree Requests' => '4f38920fe75084437f0001a9',
    'Space Savers' => '4f389210e75084437f0001d5',
    'Illegal Graffiti' => '4f38920fe75084437f0001b3',
    'Litter' => '4f389210e75084437f0001ce',
    'Overflowing Trash Can' => '4f38920fe75084437f0001ba',
    'Residential Trash out Illegally' => '56c2dc2a601827d70f000006',
    'Dead Animal Pick-up' => '4f389210e75084437f0001c4',
    'Needle Clean-up' => '55563da904853fde08a10507',
    'Rodent Sighting' => '4f38920fe75084437f000188',
    'Broken Park Equipment' => '51f6012a2debc151cb9b5672',
    'Broken Sidewalk' => '4f389210e75084437f0001d3',
    'Damaged Sign' => '4f389210e75084437f0001ec',
    'Pothole' => '4f389210e75084437f0001ca',
    'Abandoned Bicycle' => '4f38920fe75084437f00019c',
    'Abandoned Vehicle' => '4f389210e75084437f0001de',
    'Illegal Parking' => '4f389210e75084437f0001e5',
    'Park Lights' => '51f6012b2debc151cb9b5675',
    'Street Lights' => '4f389210e75084437f0001d8',
    'Traffic Signal' => '549d8f0b0485971e64c7b37b',
    'Dead Tree Removal' => '55c2aa7a601827411b000001',
    'Tree Pruning' => '4f38920fe75084437f0001b2',
    'Other' => '4f38920fe75084437f0001a0',
    'Unsafe Dangerous Conditions' => '4f38920fe75084437f000192',
];
$endpoint = 'https://mayors24.cityofboston.gov/open311/v2/requests.json?service_request_id=';
$filename = 'dangerous.json';
$first_report = 101000001836;
$last_report = 101003193500;
$i = $last_report;
$total_reports = $last_report - $first_report;
$iz = 0;

while ($i > $first_report) {
    $contents = file_get_contents($endpoint . $i);
    $json = json_decode($contents);
    $json = reset($json);
    $unsafes = [];
    if (isset($json->service_code)) {
        if ($json->service_code == '4f38920fe75084437f000192') {
            $existing_json = file_get_contents($filename);
            $data = json_decode($existing_json);
            $data[] = $json;
            file_put_contents($filename, json_encode($data));
            QueryBase::fixJson($filename);
            echo "✅ Wrote service_request_id #$i\n";
        }
    }
    else {
        echo "FYI, the service code wasn't set here, 🤔" . "\n(Request #$i)\n";
    }
    if ($i % 13 == 0) {
        echo "Done with request #" . $i . "; " . ($total_reports - $iz) . " more to go.\n";
    }
    $i--;
    $iz++;
}
