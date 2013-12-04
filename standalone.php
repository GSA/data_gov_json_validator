<?

require_once(__DIR__.'/data_gov_json_validator.module');

function composer_manager_register_autoloader()
{
    require_once(__DIR__.'/vendor/autoload.php');
}

class ResourceNotFoundException extends Exception {}

$data_file_path = __DIR__.DIRECTORY_SEPARATOR.'department_of_treasury.json';
$schema_file_path = __DIR__.DIRECTORY_SEPARATOR.'schema_1_0_final.json';

// debug mode on
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);

// 30 minutes
set_time_limit(60*30);

data_gov_json_validator($data_file_path, $schema_file_path);