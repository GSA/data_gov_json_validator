<?

$data_dir = __DIR__.'/../data/';
$json_urls_csv_path = $data_dir . 'agency_json_urls.csv';

foreach (glob($data_dir.'*.json') as $dataset) {
    unlink($dataset);
}

error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);
ini_set('memory_limit', '500M');

// 30 minutes
set_time_limit(60*30);

$row = 1;
$csv_data = explode("\n", file_get_contents($json_urls_csv_path));
foreach ($csv_data as $line) {
    $data = str_getcsv($line,',');

    echo 'Importing '.$data[0].' json  ...    ';
    ob_flush();flush();sleep(1);
    $escape_from = array(' ', '.', ',');
    $title = trim(strtolower(str_replace($escape_from, '_', $data[0]))).'.json';
    $url = $data[1];

    $invalid = false;

    if (FALSE == ($content = @file_get_contents($url))) {
        echo ' NETWORK_ERROR ' . PHP_EOL;
        continue;
        ob_flush();flush();sleep(1);
    }

    $json = json_decode($content);
    if (is_null($json)) {
        $invalid = true;
        $json = $content;
    } else {
        $json = json_encode($json, JSON_PRETTY_PRINT);
    }

    $result = file_put_contents($data_dir.$title, $json);

    echo $invalid ? ' INVALID_JSON ' : ($result ? 'SUCCESS' : 'FAIL');
    echo PHP_EOL;
    ob_flush();flush();sleep(1);
}