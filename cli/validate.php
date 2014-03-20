<?php

/**
 * WARNING
 * Before running this script:
 * 1. Download latest json datasets using download.php script
 * 2. Update latest Open Data schema to validate against it

 */
require_once dirname(__DIR__) . '/inc/common.php';

define('ENABLE_CKAN_VALIDATION', false);
define('USE_FEDERAL_SCHEMA', false);
define('SCHEMA_PATH', USE_FEDERAL_SCHEMA ? JSON_FEDERAL_SCHEMA_PATH : JSON_NON_FEDERAL_SCHEMA_PATH);
define('FED_SUFFIX', USE_FEDERAL_SCHEMA ? 'fed' : 'non-fed');


if (!is_file(SCHEMA_PATH)) {
    throw new Exception('Please get latest json schema using cli/update-schemas script');
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

foreach ($datasets as $dataset) {
    $JsonValidator->validate($dataset, SCHEMA_PATH, ENABLE_CKAN_VALIDATION);
}

// show running time on finish
timer();