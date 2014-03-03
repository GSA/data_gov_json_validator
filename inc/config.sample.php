<?php

/**
 * Agencies list info, including sub-agencies and terms
 * used to get terms tree for agency before each CKAN import
 */
define('AGENCIES_LIST_URL', 'http://idm.data.gov/fed_agency.json');

/**
 * Json schema to validate against it
 */
define('SCHEMA_URL', 'http://project-open-data.github.io/schema/1_0_final/single_entry.json');

/**
 * Resources dir for schema and json list csv
 */
define('RESOURCES_DIR', ROOT_DIR . '/resources');

/**
 * Local schema path
 */
define('JSON_SCHEMA_PATH', RESOURCES_DIR . '/schema.json');

/**
 * Data dir for keeping downloaded json datasets
 */
define('DATA_DIR', ROOT_DIR . '/data');

/**
 * CKAN API URL
 */
define('CKAN_API_URL', 'https://catalog.data.gov/api/3/');

/**
 * CKAN API KEY
 * use null if not needed
 */
define('CKAN_API_KEY', 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx'); // CKAN API KEY, if needed / or null