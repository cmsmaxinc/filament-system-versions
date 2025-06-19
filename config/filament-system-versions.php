<?php

return [
    'database' => [
        'table_name' => 'composer_versions',
    ],
    'widgets' => [
        'dependency' => [
            'show_direct_only' => true,
        ],
    ],
    'paths' => [
        'php_path' => '', // Path to the PHP executable, if default path not working
        'composer_path' => '', // Path to the Composer executable, if default path not working
    ],
];
