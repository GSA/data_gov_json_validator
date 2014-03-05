data_gov_json_validator
=======================

JSON Validator and CKAN Search

POD Schema is used for JSON validation http://project-open-data.github.io/schema/

Package search API v.3 of data.gov catalog is used for search http://catalog.data.gov/api/3/action/package_search

Full agencies and their `data.json` urls are listed on the POD Dashboard http://data.civicagency.org/offices

Installation
===

1. Download the [`composer.phar`](https://getcomposer.org/composer.phar) executable or use the installer.
    ``` sh
    $ curl -sS https://getcomposer.org/installer | php
    ```

2. Run Composer:
    ``` sh
    $ php composer.phar install
    ```

3. Copy `inc/config.sample.php` to `inc/config.php` and check its values

Getting JSON files from the agencies
===

1. Check and update `resources/agency_json_urls.csv`. The format is simple: `"AGENCY_TITLE", json_url`
    ```
    "Department of Agriculture",http://www.usda.gov/data.json
    "Department of Education",http://www.ed.gov/data.json
    "Department of Energy",http://www.energy.gov/data.json
    ```

2. To download latest JSONs run
    ``` sh
    $ php cli/download.php
    ```


The `data/agency_json_download.log` will contain overall statistics about latest json update, ex.:
```
Importing Department of Defense json .  .  .  .  .  .  .  .  .  .  .  NETWORK ERROR 404
Importing Department of Education json .  .  .  .  .  .  .  .  .  .  .SUCCESS (FIXED UTF8)
Importing Department of Labor json .  .  .  .  .  .  .  .  .  .  .  . SUCCESS
Importing Department of the Interior json .  .  .  .  .  .  .  .  .  .SUCCESS (REPLACED "][" WITH "," )
Importing Department of the Treasury json .  .  .  .  .  .  .  .  .  .SUCCESS
Importing Department of Transportation json .  .  .  .  .  .  .  .  . INVALID_JSON - Syntax error, malformed JSON
Importing Department of Veterans Affairs json .  .  .  .  .  .  .  .  SUCCESS
Importing General Services Administration json .  .  .  .  .  .  .  . SUCCESS (REMOVED BOM)
```

Updating JSON Schema
===
To get latest schema from
http://project-open-data.github.io/schema/1_0_final/single_entry.json run
``` sh
$ php cli/update-schema.php
```

Validation and CKAN search
===

Before running the script, think of [updating schema.json](#updating-json-schema) and updating dependencies:
``` sh
$ php composer.phar update
```

1. Put all your JSON datasets to `/data/` folder OR [download them using download.php](#getting-json-files-from-the-agencies)

  Files must be in JSON, named by `*.json` pattern
  * `example1.json`
  * `department_treasury.json`
  * `last_department.json`


2. Run script
    ``` sh
    $ php cli/validate.php
    ```

3. Grab the results from `/results/{date}_VALIDATION folder`

   The results will be called using data files name, with _results postfix.
  * `example1_results.json`
  * `example1_results.csv`
  * `department_treasury_results.json`
  * `department_treasury_results.csv`
  * `last_department_results.json`
  * `last_department_results.csv`

  `Json` file contains json validation information, and `csv` file has CKAN search results

  The `processing.log` in same folder will give you some overall statistics information.


Links
===
1. JSON online editor (http://www.jsoneditoronline.org)
2. POD online json validator (http://project-open-data.github.io/json-validator/)
3. The CKAN API Documentation (http://docs.ckan.org/en/latest/api.html#ckan.logic.action.get.package_search)
