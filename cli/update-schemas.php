<?php

require_once dirname(__DIR__) . '/inc/common.php';

/**
 * Getting federal schema
 */

try {
    if (false === $schema = getSSLPage(FEDERAL_SCHEMA_URL)) {
        throw new Exception('Fatal Error: Could not get federal schema by url ' . FEDERAL_SCHEMA_URL . PHP_EOL);
    }

    if (strpos($schema, 'json-schema')) {
        if (false === file_put_contents(JSON_FEDERAL_SCHEMA_PATH, $schema)) {
            throw new Exception('Fatal Error: Could not write to file ' . JSON_FEDERAL_SCHEMA_PATH . PHP_EOL);
        }
        echo 'Success: Federal json schema has been updated' . PHP_EOL;
    } else {
        throw new Exception('Fatal Error: Could not find control phrase "json-schema" on ' . FEDERAL_SCHEMA_URL . PHP_EOL);
    }
} catch (Exception $ex) {
    echo 'Fatal:' . $ex->getMessage() . PHP_EOL;
}
/**
 * Getting non-federal schema
 */
try {
    if (false === $schema = getSSLPage(NON_FEDERAL_SCHEMA_URL)) {
        throw new Exception('Fatal Error: Could not get non-federal schema by url ' . NON_FEDERAL_SCHEMA_URL . PHP_EOL);
    }

    if (strpos($schema, 'json-schema')) {
        if (false === file_put_contents(JSON_NON_FEDERAL_SCHEMA_PATH, $schema)) {
            throw new Exception('Fatal Error: Could not write to file ' . JSON_NON_FEDERAL_SCHEMA_PATH . PHP_EOL);
        }
        echo 'Success: Federal json schema has been updated' . PHP_EOL;
    } else {
        throw new Exception('Fatal Error: Could not find control phrase "json-schema" on ' . NON_FEDERAL_SCHEMA_URL . PHP_EOL);
    }
} catch (Exception $ex) {
    echo 'Fatal:' . $ex->getMessage() . PHP_EOL;
}

/**
 * @param $url
 * @return mixed
 */
function getSSLPage($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSLVERSION, 3);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}

/**
 * Output script run time
 */
timer();