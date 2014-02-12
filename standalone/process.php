<?

/**
 * WARNING
 *
 * Before running this script itself, please download latest json datasets
 * using download.php script.
 */

require_once(__DIR__ . '/../lib/bootstrap.php');

$data_dir = __DIR__.'/../data/';
$results_dir = __DIR__.'/../results/';

$error_log_path = __DIR__.'/../results/processing.log';
define('RESULTS_LOG', $error_log_path);

is_file(RESULTS_LOG) && unlink(RESULTS_LOG);

foreach (glob($results_dir.'*.csv') as $dataset) {
    unlink($dataset);
}

foreach (glob($results_dir.'*.json') as $dataset) {
    unlink($dataset);
}

$start = time();

$datasets = glob($data_dir.'*.json');
sort($datasets);

foreach ($datasets as $dataset) {
    data_gov_json_validator($dataset, JSON_SCHEMA_PATH, true);
}

$finish = time();
echo PHP_EOL.(($finish-$start)/60).' minutes '.(($finish-$start)%60).' seconds '.PHP_EOL;

?>done