<?php

/**
 * WARNING
 * Before running this script:
 * 1. Download latest json datasets using download.php script
 * 2. Update latest Open Data schema to validate against it

 */
require_once dirname(__DIR__) . '/inc/common.php';

define('ENABLE_CKAN_VALIDATION', false);

if (!is_file(JSON_FEDERAL_SCHEMA_PATH) || !is_file(JSON_NON_FEDERAL_SCHEMA_PATH)) {
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
    $JsonValidator->validate($dataset, JSON_FEDERAL_SCHEMA_PATH, ENABLE_CKAN_VALIDATION, 'fed');
    $JsonValidator->validate($dataset, JSON_NON_FEDERAL_SCHEMA_PATH, ENABLE_CKAN_VALIDATION, 'non-fed');
}

// show running time on finish
timer();