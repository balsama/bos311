#!/usr/bin/env php
<?php
include_once('src/QueryBase.php');

$query = new QueryBase();

//$query->setSourceFileNumber(2, 1);

$query->filterRecordsByDescription(['sidewalk']);

$query->setImageUrls($query->getMatches(['media_url']));

$query->downloadImages('13');