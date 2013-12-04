data_gov_json_validator
=======================

JSON Validator and CKAN Search

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
  * `department_treasury_results.json`
  * `last_department_results.json`


Links
===
1. JSON online editor (http://www.jsoneditoronline.org)
2. POD online json validator (http://project-open-data.github.io/json-validator/)
