<?php

return [
    'database' => [
        'table_name' => 'composer_versions',
    ],
    'paths' => [
        'php_path' => env('PHP_PATH', ''), // Path to the PHP executable, if default path not working
        'composer_path' => env('COMPOSER_PATH', ''), // Path to the Composer executable, if default path not working
        'node_path' => env('NODE_PATH', ''), // Path to the Node.js executable, if default path not working
        'npm_path' => env('NPM_PATH', ''), // Path to the npm executable, if default path not working
    ],
    'inventory' => [
        'composer_lock' => 'composer.lock',
        'package_json' => 'package.json',
        'package_lock' => 'package-lock.json',
        'runtime_cache_seconds' => 3600,
    ],
    'technologies' => [],
];
