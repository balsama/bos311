#!/usr/bin/env php
<?php
include_once('src/QueryBase.php');

$query = new QueryBase('other');
$query->filterRecordsByFieldContains('status', ['closed']);
$matchesAll = $query->getMatches();
$matchesStatusNoted = $query->getMatches(['status_notes']);
