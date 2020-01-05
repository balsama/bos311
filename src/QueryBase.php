<?php

class QueryBase
{
    private $filenames = [];
    private $active_filenames = [];
    private $matches = [];
    private $imageUrls = [];

    public function __construct($allowNonJson = false)
    {
        $this->filenames = $this->findFilenames($allowNonJson);
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
     * @param $searchTerms array
     *   The terms to search for in the description field.
     */
    public function filterRecordsByDescription($searchTerms = []) {
        foreach ($this->active_filenames as $filename) {
            $data = $this->loadFileJson($filename);
            foreach ($data as $record) {
                if (property_exists($record, 'description')) {
                    if ($this->str_contains_all($record->description, $searchTerms)) {
                        $this->matches[] = $record;
                    }
                }
            }
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
            if (strpos($haystack, $needle) === false) {
                return false;
            }
        }
        return true;
    }

    private function loadFileJson($filename) {
        $contents = file_get_contents($filename);
        return json_decode($contents);
    }

    private function findFilenames($allowNonJson = false) {
        $dir = 'data/';
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
            $fixedFilenames[] = 'data/' . $filename;
        }

        return $fixedFilenames;
    }

    private function filterjson($str) {
        if ($this->endsWith($str, '.json')) {
            return true;
        }
        return false;
    }

    private function endsWith($haystack, $needle)
    {
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

        file_put_contents($filename, $contents);
    }
}
