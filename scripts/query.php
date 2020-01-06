#!/usr/bin/env php
<?php
include_once('src/QueryBase.php');

$query = new QueryBase();

$query->setSourceFileNumber(2, 1);

$query->filterRecordsByFieldContains('description', ['sidewalk']);
$query->filterRecordsByFieldContains('description', ['scooter'], true);
$query->filterRecordsByFieldContains('description', ['motorcycle'], true);

$totalViolations = count($query->getMatches(['service_request_id']));
$query->filterRecordsByFieldContains('status_notes', ['tagged']);
$violationsTagged = count($query->getMatches(['service_request_id']));
$percentTagged = ($violationsTagged / $totalViolations) * 100;

print "Total violations: $totalViolations; Number tagged: $violationsTagged; Percentage tagged: $percentTagged";
