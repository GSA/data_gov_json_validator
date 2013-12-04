<?

require_once(__DIR__ . '/bootstrap.php');

$data_dir = __DIR__.'/../data/';

foreach (glob($data_dir.'*.json') as $dataset) {
    data_gov_json_validator($dataset, JSON_SCHEMA_PATH);
}