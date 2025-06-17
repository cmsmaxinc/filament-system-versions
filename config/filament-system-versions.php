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
        'php_path' => 'php', // Path to the PHP executable
        'composer_path' => 'composer', // Path to the Composer executable
    ],
];
