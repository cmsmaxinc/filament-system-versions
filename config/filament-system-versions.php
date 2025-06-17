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
        'php_path' => 'php', //you can find path with 'which php' command
        'composer_path' => 'composer', //you can find path with 'which composer' command
    ],
];
