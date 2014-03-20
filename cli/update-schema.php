<?php

require_once dirname(__DIR__) . '/inc/common.php';

if (false === $schema = file_get_contents(SCHEMA_URL)) {
    throw new Exception('Fatal Error: Could not get schema by url ' . SCHEMA_URL . PHP_EOL);
}

if (strpos($schema, 'json-schema')) {
    if (false === file_put_contents(JSON_SCHEMA_PATH, $schema)) {
        throw new Exception('Fatal Error: Could not write to file ' . JSON_SCHEMA_PATH . PHP_EOL);
    }
    timer();
    die('Success: Json schema has been updated' . PHP_EOL);
} else {
    throw new Exception('Fatal Error: Could not find control phrase "json-schema" on ' . SCHEMA_URL . PHP_EOL);
}
?>
fail
