<?php

// debug mode on
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 30 minutes
set_time_limit(60 * 30);
ini_set('memory_limit', '1500M');

date_default_timezone_set('EST');

define('ROOT_DIR', dirname(__DIR__));
define('RESULTS_DIR', ROOT_DIR . '/results');

if (!is_dir(ROOT_DIR . '/vendor')) {
    throw new Exception('Install dependencies via composer');
}

require ROOT_DIR . '/vendor/autoload.php';

if (!is_file(ROOT_DIR . '/inc/config.php')) {
    throw new Exception('Please copy inc/config.sample.php to inc/config.php and check its values');
}

require ROOT_DIR . '/inc/config.php';