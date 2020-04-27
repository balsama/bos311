#!/usr/bin/env php
<?php

require_once 'vendor/autoload.php';

$filename = '/Users/butler/bos311/data/updated-other/updated-other--ai';

\Balsama\Helpers::fixJson($filename);

echo "Fixed $filename \n";