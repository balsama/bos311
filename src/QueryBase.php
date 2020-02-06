<?php

class QueryBase
{
    private $filenames = [];
    private $active_filenames = [];
    private $matches = [];
    private $imageUrls = [];

    /**
     * QueryBase constructor.
     * @param string $dataFolder
     *   The folder within data that has the records your interested in.
     * @param bool $allowNonJson
     */
    public function __construct($dataFolder = 'illegal-parking', $allowNonJson = false)
    {
        $this->filenames = $this->findFilenames($dataFolder, $allowNonJson);
        $this->active_filenames = $this->filenames;
    }

    /**
     * Set the number of source files you want to use in your query. Useful if
     * you are quickly iterating and don't want to run the query on the whole
     * dataset.
     *
     * @param $number int
     *   The number of files to search.
     *
     * @param $offset int
     *   The number to offset from the beginning.
     */
    public function setSourceFileNumber($number, $offset = 0) {
        $sourceFileNames = array_values($this->filenames);
        $outputFileNames = [];
        $i = 0;
        while ($i < $number) {
            $outputFileNames[] = $sourceFileNames[$i + $offset];
            $i++;
        }
        $this->active_filenames = $outputFileNames;
    }

    /**
     * Filter the records in the dataset by keywords that appear in the
     * `description` field. Currently this search operates as and "ALL" search.
     * That is, all words provided in the array must appear at least once in
     * the description.
     *
     * @param $field string
     *   The property of the record on which to search.
     * @param $searchTerms array
     *   The terms to search for in the description field.
     * @param $exclude bool
     *   Whether the filter should include or exclude records.
     * @param $add bool
     *   Whether to add new matches to an existing set of records.
     */
    public function filterRecordsByFieldContains($field = 'description', $searchTerms = [], $exclude = false, $add = false) {
        $records = [];

        if (empty($this->matches) || $add) {
            // First time this is run or if we want to scan all again, load/use
            // all active files
            $records = $this->getAllRecords();
        }
        else {
            // We've already refined the list. We're just further refining now.
            $records = array_merge($records, $this->matches);
        }
        $currentMatches = [];
        foreach ($records as $record) {
            if (property_exists($record, $field)) {
                if ($this->str_contains_all($record->$field, $searchTerms)) {
                    if ($exclude) {
                        unset($currentMatches[$record->service_request_id]);
                    }
                    else {
                        $currentMatches[$record->service_request_id] = $record;
                    }
                }
            }
        }
        if ($add) {
            $this->matches = array_merge($currentMatches, $this->matches);
        }
        else {
            $this->matches = $currentMatches;
        }
        $this->matches = $this->dedupeMatches();
    }

    /**
     * Returns the filtered records based on the results of
     * `filterRecordsByDescription`. Will be empty enless the prior method has
     * been called.
     *
     * @param null|array $fields
     *   The fields you want returned from the record. If left empty, the whole
     *   record will be returned with all fields.
     *
     * @return array|array[]
     *   If a single field is provided in the $fields array param, then an
     *   array is returned with values of that field.
     *
     *   If more than one field is provided in the $fields array param, then
     *   an array of arrays containing the fields, keyed by the field name, is
     *   returned.
     */
    public function getMatches($fields = null) {
        if ($fields === null) {
            return $this->matches;
        }
        elseif (count($fields) === 1) {
            $field = reset($fields);
            $returns = [];
            foreach ($this->matches as $match) {
                if (property_exists($match, $field)) {
                    $returns[] = $match->$field;
                }
            }
            return $returns;
        }
        else {
            if (count($fields) < 2) {
                throw new \http\Exception\InvalidArgumentException('not null, one or countable');
            }
            $i = 0;
            $returns = [];
            foreach ($this->matches as $match) {
                $returns[$i] = [];
                foreach ($fields as $field) {
                    if (property_exists($match, $field)) {
                        $returns[$i][$field] = $match->$field;
                    }
                }
                $i++;
            }
            return $returns;
        }
    }

    /**
     * Download all images in the $this->imageUrls var into the photos folder.
     *
     * @param int $i
     *   Starting number for the downloaded files filenames.
     * @param string $path
     * @param string $prefix
     */
    public function downloadImages($i=1, $path = 'photos/', $prefix = 'photo-') {
        $count = count ($this->imageUrls);
        foreach ($this->imageUrls as $imageUrl) {
            echo 'Downloading image ' . "$i of $count" . ' from: ' . $imageUrl;
            $raw = file_get_contents($imageUrl);
            file_put_contents($path . $prefix . $i . '.jpg', $raw);
            echo " Done.\n";
            $i++;
        }
    }

    /**
     * @param $imageUrls array
     *   An array of image URLs.
     */
    public function setImageUrls($imageUrls) {
        $this->imageUrls = $imageUrls;
    }

    private function dedupeMatches() {
        return array_unique($this->matches, SORT_REGULAR);
    }

    private function str_contains_all($haystack, array $needles) {
        foreach ($needles as $needle) {
            if (strpos(strtolower($haystack), strtolower($needle)) === false) {
                return false;
            }
        }
        return true;
    }

    private function loadFileJson($filename) {
        $contents = file_get_contents($filename);
        return json_decode($contents);
    }

  /**
   * @param string $folder
   *   The name of the folder inside ./data/ which contains the responses to
   *   search.
   * @param bool $allowNonJson
   *   Include non-json files in the results.
   *
   * @return string[]
   *   An array of filenames with path.
   *
   */
    private function findFilenames($folder, $allowNonJson = false) {
        $dir = getcwd() . "/data/$folder/";
        $filenames = array_diff(scandir($dir), ['..', '.', '.DS_Store']);
        foreach ($filenames as $key => $link) {
            if (is_dir($dir.$link)) {
                unset($filenames[$key]);
            }
        }
        if ($allowNonJson === false) {
            $filenames = array_filter($filenames, 'QueryBase::filterjson');
        }
        $fixedFilenames = [];
        foreach ($filenames as $filename) {
            $fixedFilenames[] = "data/$folder/" . $filename;
        }

        return $fixedFilenames;
    }

    private function getAllRecords() {
        $records = [];
        foreach ($this->active_filenames as $filename) {
            $newRecords = $this->loadFileJson($filename);
            $records = array_merge($records, $newRecords);
        }
        return $records;
    }

    private function filterjson($str) {
        if ($this->endsWith($str, '.json')) {
            return true;
        }
        return false;
    }

    private function endsWith($haystack, $needle) {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }

    public function getFilenames() {
        return $this->filenames;
    }

    public function getActiveFilenames() {
        return $this->active_filenames;
    }

    public function getImageUrls() {
        return $this->imageUrls;
    }

    public function validateJson($filename) {
        $contents = file_get_contents($filename);
        $json = json_decode($contents);
        if ($json === null) {
            return false;
        }
        return true;
    }

    public function fixJson($filename) {
        $contents = '[' . file_get_contents($filename) . ']';
        $contents = str_replace('[[', '[', $contents);
        $contents = str_replace("},\n]", '}]', $contents);
        $contents = str_replace('],[', ',', $contents);
        $contents = str_replace('][', '', $contents);
        $contents = str_replace('}{', '},{', $contents);
        $contents = str_replace(']]', ']', $contents);

        file_put_contents($filename . '.json', $contents);
    }

    /**
     * @param $time stdClass
     * @return integer
     */
    public function getDaysOpen($time) {
        $days = $time->diff->format('%a');
        return (int) $days;
    }

    /**
     * @param $match stdClass
     *   An unfiltered match from QueryBase Class that has `requested_` and `updated_` datetimes properties.
     * @return stdClass
     * @throws Exception
     */
    public static function getTimesFromMatch($match) {
        $open = new DateTime($match->requested_datetime);
        $closed = new DateTime($match->updated_datetime);
        $times = new stdClass();
        $times->open = $open;
        $times->closed = $closed;
        $times->diff = self::getDiff($open, $closed);

        return $times;
    }

    /**
     * Difference in time between two DateTimes
     *
     * @param $open DateTime
     * @param $closed DateTime
     * @return DateInterval
     */
    public static function getDiff($open, $closed) {
        $diff = $open->diff($closed);
        return $diff;
    }

    /**
     * @param $numbers int[]
     * @return float|int
     */
    public static function getMeanAverage($numbers) {
        $sum = array_sum($numbers);
        $count = count($numbers);
        $average = ($sum / $count);

        return $average;
    }

    /**
     * @param $fileName string
     * @param $records array
     */
    public static function writeToCSVFile($fileName, $records)
    {
        $fp = fopen($fileName . '.csv', 'w');
        foreach ($records as $record) {
            fputcsv($fp, $record);
        }
        fclose($fp);
    }

}
