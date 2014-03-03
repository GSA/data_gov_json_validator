<?php

require_once dirname(__DIR__) . '/inc/common.php';

$DatasetImporter = new CKAN\JsonValidator\DatasetImporter();

/**
 * Clean data dir before importing new datasets
 */
$DatasetImporter->cleaner();

$log = '';

foreach (glob(RESOURCES_DIR . '/*.csv') as $json_urls_csv_path) {

    echo $out = PHP_EOL . str_pad('Now processing ' . basename($json_urls_csv_path), 80, ' . ') . PHP_EOL . PHP_EOL;
    $log .= $out;

    $row      = 1;
    $csv_data = explode("\n", file_get_contents($json_urls_csv_path));
    foreach ($csv_data as $line) {
        if (!strlen($line = trim($line))) {
            continue;
        }
        list($agency, $json_url) = str_getcsv($line, ',');

        $DatasetImporter->import($agency, $json_url, $log);
    }
}

file_put_contents(DATA_DIR . '/agency_json_download.log', $log);

?>
done
