<?php

/**
 * Agencies list info, including sub-agencies and terms
 * used to get terms tree for agency before each CKAN import
 */
define('AGENCIES_LIST_URL', 'http://idm.data.gov/fed_agency.json');

/**
 * Json schema to validate against it
 */
#3 POD Schema Variants: Federal: https://github.com/GSA/pod-schema-variants/tree/master/v1.0/federal
define('FEDERAL_SCHEMA_URL', 'https://raw.githubusercontent.com/GSA/pod-schema-variants/master/v1.0/federal/single_entry.json');

#6 POD Schema Variants: Non-Federal (temp) :https://github.com/philipashlock/farm-server/tree/master/schema/non-federal-temp
define('NON_FEDERAL_SCHEMA_URL', 'https://raw.githubusercontent.com/GSA/project-open-data-dashboard/master/schema/non-federal-temp/single_entry.json');

/**
 * Resources dir for schema and json list csv
 */
define('RESOURCES_DIR', ROOT_DIR . '/resources');

/**
 * Local schema path
 */
define('JSON_FEDERAL_SCHEMA_PATH', RESOURCES_DIR . '/federal_schema.json');
define('JSON_NON_FEDERAL_SCHEMA_PATH', RESOURCES_DIR . '/non_federal_schema.json');

/**
 * Data dir for keeping downloaded json datasets
 */
define('DATA_DIR', ROOT_DIR . '/data');

/**
 * CKAN API URL
 */
define('CKAN_API_URL', 'https://catalog.data.gov/api/3/');

define('CKAN_STAGING_API_URL', 'http://staging.catalog.data.gov/api/3/');