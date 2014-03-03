<?php
/**
 * @author Alex Perfilov
 * @date   2/27/14
 */

namespace CKAN\JsonValidator;


/**
 * Class DatasetImporter
 * @package CKAN\JsonValidator
 */
class DatasetImporter
{
    /**
     * Clean data dir before importing new datasets
     */
    public function cleaner()
    {
        foreach (glob(DATA_DIR . '/*.json') as $dataset) {
            unlink($dataset);
        }
    }

    /**
     * @param $agency
     * @param $json_url
     * @param $log
     * @return bool
     */
    public function import($agency, $json_url, &$log)
    {
        echo $out = str_pad('Importing ' . $agency . ' json', 70, ' . ');
        $log .= $out;

        $jsonFile = $this->niceFilename($agency);

        $invalid = false;
        $fixed   = false;

        $jsonPath = DATA_DIR . '/' . $jsonFile;
        if (!copy($json_url, $jsonPath) && !$this->stream_copy($json_url, $jsonPath)) {
            $error = 'NETWORK ERROR 404' . PHP_EOL;
            echo $error;
            $log .= $error;
            is_file($jsonPath) && unlink($jsonPath);

            return false;
        }
        $content = file_get_contents($jsonPath);

        $try        = json_decode($content);
        $json_error = '';

        if (is_null($try)) {
            $json    = $content;
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
                $json  = json_encode($try, JSON_PRETTY_PRINT);
                $fixed = 'SUCCESS (FIXED UTF8)';
            }

            if (!$fixed) {
                $a   = trim(iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode(trim(preg_replace('/\x{EF}\x{BB}\x{BF}/', '', $content)))));
                $try = json_decode($a);
                if (!is_null($try)) {
                    $json  = json_encode($try, JSON_PRETTY_PRINT);
                    $fixed = 'SUCCESS (REMOVED BOM)';
                }
            }

            if (!$fixed) {
                $a   = trim(preg_replace('/\x{EF}\x{BB}\x{BF}/', '', $content));
                $try = json_decode($a);
                if (!is_null($try)) {
                    $json  = json_encode($try, JSON_PRETTY_PRINT);
                    $fixed = 'SUCCESS (REMOVED BOM AND FIXED UTF8)';
                }
            }

            if (!$fixed && (strrpos($content, ']['))) {
                $a   = str_replace('][', ',', $content);
                $a   = trim(iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode(trim(preg_replace('/\x{EF}\x{BB}\x{BF}/', '', $a)))));
                $try = json_decode($a);
                if (!is_null($try)) {
                    $json  = json_encode($try, JSON_PRETTY_PRINT);
                    $fixed = 'SUCCESS (REPLACED "][" WITH "," )';
                }
            }

        } else {
            $json = json_encode($try, JSON_PRETTY_PRINT);
        }

        $result = file_put_contents($jsonPath, $json);

        $out = $invalid ? ($fixed ? : 'INVALID_JSON' . $json_error) : ($result ? 'SUCCESS' : 'FAIL' . $json_error);
        echo $out . PHP_EOL;
        $log .= $out . PHP_EOL;

        return true;
    }

    /**
     * @param $agency
     * @return string
     */
    private function niceFilename($agency)
    {
        $escape_from = [' ', '.', ','];

        return trim(strtolower(str_replace($escape_from, '_', $agency))) . '.json';
    }

    /**
     * @param $src
     * @param $dest
     * @return bool|int
     */
    private function stream_copy($src, $dest)
    {
        $file_source      = fopen($src, 'r');
        $file_destination = fopen($dest, 'w+');
        if (!$file_source || !$file_destination) {
            return false;
        }
        $len = stream_copy_to_stream($file_source, $file_destination);
        fclose($file_source);
        fclose($file_destination);

        return $len;
    }
} 