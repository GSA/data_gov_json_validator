data_gov_json_validator
=======================

JSON Validator and CKAN Search

POD Schema is used for JSON validation http://project-open-data.github.io/schema/

Package search API v.3 of data.gov catalog is used for search http://catalog.data.gov/api/3/action/package_search
Documentation here http://docs.ckan.org/en/latest/api.html#ckan.logic.action.get.package_search

Installation
===

1. Download the [`composer.phar`](https://getcomposer.org/composer.phar) executable or use the installer.

    ``` sh
    $ curl -sS https://getcomposer.org/installer | php
    ```

2. Run Composer: `php composer.phar install`

Usage
===
You can use it as a drupal module, or as a standalone script.

1. Put all your JSON datasets to /data/ folder

  Files must be in JSON, named by *.json pattern
  * `example1.json`
  * `department_treasury.json`
  * `last_department.json`


2. Run script

   For a standalone version, just run `./standalone/index.php` in your browser or console.

3. Grab the results from /results/ folder

   The results will be called using data files name, with _results postfix:
  * `example1_results.json`
  * `example1_results.csv`
  * `department_treasury_results.json`
  * `department_treasury_results.csv`
  * `last_department_results.json`
  * `last_department_results.csv`

  The `processing.log` in same folder will give you some overall statistics information.


Links
===
1. JSON online editor (http://www.jsoneditoronline.org)
2. POD online json validator (http://project-open-data.github.io/json-validator/)
