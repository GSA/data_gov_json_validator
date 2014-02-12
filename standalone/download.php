<?php

$data_dir = __DIR__ . '/../data/';
$config_dir = __DIR__ . '/../config/';
$json_urls_csv_path = $config_dir . 'agency_json_urls.csv';

define('ONLY_TEST_LOCAL_DATASETS', in_array('test', $argv));

if (!ONLY_TEST_LOCAL_DATASETS) {
    foreach (glob($data_dir . '*.json') as $dataset) {
        unlink($dataset);
    }
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '2000M');

// 30 minutes
set_time_limit(60 * 30);

$log = '';

$row = 1;
$csv_data = explode("\n", file_get_contents($json_urls_csv_path));
foreach ($csv_data as $line) {
    if (!strlen($line = trim($line))) {
        continue;
    }
    $data  = str_getcsv($line, ',');

    echo $out = str_pad('Importing ' . $data[0] . ' json', 70, ' . ');
    $log .= $out;
    $escape_from = array(' ', '.', ',');
    $title = trim(strtolower(str_replace($escape_from, '_', $data[0]))) . '.json';
    $url   = $data[1];

    $invalid = false;
    $fixed = false;
    $network = true;

    if (!ONLY_TEST_LOCAL_DATASETS) {
        if (@!copy($url, $data_dir . $title) && !stream_copy($url, $data_dir . $title)) {
            $error = 'NETWORK ERROR 404' . PHP_EOL;
            echo $error;
            $log .= $error;
            is_file($data_dir . $title) && unlink($data_dir . $title);
            continue;
        }
    }

    $content   = @file_get_contents($data_dir . $title);

    $try = json_decode($content);
    $json_error = '';

    if (is_null($try)) {
        $json = $content;
        $invalid = true;

        switch (json_last_error()) {
            case JSON_ERROR_DEPTH:
                $json_error = ' - Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $json_error = ' - Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $json_error = ' - Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $json_error = ' - Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                $json_error = ' - Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                $json_error = ' - Unknown error';
                break;
        }

        $try = iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($content));
        $try = json_decode($try, JSON_UNESCAPED_SLASHES);
        if (!is_null($try)) {
            $json = json_encode($try, JSON_PRETTY_PRINT);
            $fixed = 'SUCCESS (FIXED UTF8)';
        }

        if (!$fixed) {
            $a = trim(iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode(trim(preg_replace('/\x{EF}\x{BB}\x{BF}/', '', $content)))));
            $try = json_decode($a);
            if (!is_null($try)) {
                $json = json_encode($try, JSON_PRETTY_PRINT);
                $fixed = 'SUCCESS (REMOVED BOM)';
            }
        }

        if (!$fixed) {
            $a = trim(preg_replace('/\x{EF}\x{BB}\x{BF}/', '', $content));
            $try = json_decode($a);
            if (!is_null($try)) {
                $json = json_encode($try, JSON_PRETTY_PRINT);
                $fixed = 'SUCCESS (REMOVED BOM AND FIXED UTF8)';
            }
        }

        if (!$fixed && (strrpos($content, ']['))) {
            $a = str_replace('][', ',', $content);
            $a = trim(iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode(trim(preg_replace('/\x{EF}\x{BB}\x{BF}/', '', $a)))));
            $try = json_decode($a);
            if (!is_null($try)) {
                $json = json_encode($try, JSON_PRETTY_PRINT);
                $fixed = 'SUCCESS (REPLACED "][" WITH "," )';
            }
        }

    } else {
        $json = json_encode($try, JSON_PRETTY_PRINT);
    }

    $result = file_put_contents($data_dir . $title, $json);

    $out = $invalid ? ($fixed ? : 'INVALID_JSON' . $json_error) : ($result ? 'SUCCESS' : 'FAIL' . $json_error);
    echo $out . PHP_EOL;
    $log .= $out . PHP_EOL;
}

file_put_contents($data_dir . 'agency_json_download.log', $log);

function stream_copy($src, $dest)
{
    @$fsrc = fopen($src, 'r');
    @$fdest = fopen($dest, 'w+');
    if (!$fsrc || !$fdest) {
        return false;
    }
    $len = stream_copy_to_stream($fsrc, $fdest);
    fclose($fsrc);
    fclose($fdest);

    return $len;
}

?>
done
