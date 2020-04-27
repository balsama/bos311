<?php

namespace Balsama;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\ServerException;

class Helpers
{

    public static function fetch($url, $retryOnError = true)
    {
        $client = new Client();
        try {
            /**
             * @var $response ResponseInterface $response
             */
            $response = $client->get($url);
            $body = $response->getBody();
            $body = \GuzzleHttp\json_decode($body);
            $body = \GuzzleHttp\json_encode($body);
            return $body;
        } catch (ServerException $e) {
            if ($retryOnError) {
                return self::fetch($url, $retryOnError);
            }
            echo 'Caught response: ' . $e->getResponse()->getStatusCode();
        }
    }

    public static function fixJson($filename, $append = false) {
        $contents = '[' . file_get_contents($filename) . ']';
        $contents = str_replace('[[', '[', $contents);
        $contents = str_replace("},\n]", '}]', $contents);
        $contents = str_replace('],[', ',', $contents);
        $contents = str_replace('][', '', $contents);
        $contents = str_replace('}{', '},{', $contents);
        $contents = str_replace(']]', ']', $contents);
        $contents = str_replace('][', ',', $contents);
        $contents = str_replace('},{', "},\n{", $contents);

        if ($append) {
            file_put_contents($filename . $append, $contents);
            return;
        }

        if (strpos($filename, '.json') === false) {
            $filename = $filename . '.json';
        }

        file_put_contents($filename, $contents);
    }

    public static function validateJson($filename) {
        $contents = file_get_contents($filename);
        $json = json_decode($contents);
        if ($json === null) {
            return false;
        }
        return true;
    }

    /**
     * @param $fileName string
     * @param $records array
     */
    public static function writeToCSVFile($fileName, $records)
    {
        $fp = fopen($fileName . '.csv', 'w');
        foreach ($records as $month => $record) {
            fputcsv($fp, [$month, $record]);
        }
        fclose($fp);
    }

}