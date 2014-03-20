<?php

/**
 * WARNING
 * Before running this script:
 * 1. Download latest json datasets using download.php script
 * 2. Update latest Open Data schema to validate against it

 */
require_once dirname(__DIR__) . '/inc/common.php';

if (!is_file(JSON_SCHEMA_PATH)) {
    throw new Exception('Please get latest json schema using cli/update-schema script');
}

/**
 * Create results dir for logs and csv/json results
 */
$results_dir = RESULTS_DIR . date('/Ymd-His') . '_VALIDATION';
mkdir($results_dir);
define('RESULTS_DIR_YMD', $results_dir);

define('RESULTS_LOG', RESULTS_DIR_YMD . '/processing.log');

/**
 * Production
 */
$JsonValidator = new \CKAN\JsonValidator\JsonValidator(CKAN_API_URL);

/**
 * Staging
 */
//$JsonValidator = new \CKAN\JsonValidator\JsonValidator(CKAN_STAGING_API_URL);

$JsonValidator->clear();

$datasets = glob(DATA_DIR . '/*.json');
sort($datasets);

define('ENABLE_CKAN_VALIDATION', false);

foreach ($datasets as $dataset) {
    $JsonValidator->validate($dataset, JSON_SCHEMA_PATH, ENABLE_CKAN_VALIDATION);
}

// show running time on finish
timer();