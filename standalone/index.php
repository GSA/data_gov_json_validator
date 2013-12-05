<?

require_once(__DIR__ . '/bootstrap.php');

$data_dir = __DIR__.'/../data/';

$error_log_path = __DIR__.'/../results/processing.log';
define('RESULTS_LOG', $error_log_path);
is_file(RESULTS_LOG) && unlink(RESULTS_LOG);

foreach (glob($data_dir.'*.json') as $dataset) {
    data_gov_json_validator($dataset, JSON_SCHEMA_PATH, true);
}

?>done