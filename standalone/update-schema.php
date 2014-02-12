<?php

define('SCHEMA_URL', 'https://raw.github.com/project-open-data/project-open-data.github.io/master/schema/1_0_final/single_entry.json');
define('JSON_SCHEMA_PATH', __DIR__ . '/../config/schema.json');

if (false === $schema = file_get_contents(SCHEMA_URL)) {
    die('Fatal Error: Could not get schema by url ' . SCHEMA_URL . PHP_EOL);
}

if (strpos($schema, 'json-schema')) {
    if (false === file_put_contents(JSON_SCHEMA_PATH, $schema)) {
        die('Fatal Error: Could not write to file ' . JSON_SCHEMA_PATH . PHP_EOL);
    }
    die('Success: Json schema has been updated' . PHP_EOL);
} else {
    die('Fatal Error: Could not find control phrase "json-schema" on ' . SCHEMA_URL . PHP_EOL);
}
?>
done
