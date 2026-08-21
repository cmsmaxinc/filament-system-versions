<?php

return [
    'navigation' => [
        'label' => 'System Versions',
        'group' => 'Settings',
    ],
    'widgets' => [
        'dependency' => [
            'heading' => 'Packages',
            'description' => 'A list of packages with available updates',
            'empty' => 'No packages with available updates',
            'abandoned' => 'Abandoned',
            'no_data' => 'No dependency data yet. Run the dependency:versions Artisan command to collect it.',
            'missing_table' => 'The composer versions table has not been migrated yet. Publish and run this package\'s migrations first.',
            'table' => [
                'name' => 'Name',
                'version' => 'Version',
            ],
        ],
        'system' => [
            'heading' => 'System details',
            'description' => 'A summary of the system environment',
            'details' => [
                'environment' => 'Environment',
                'timezone' => 'Timezone',
                'debug' => 'Debug mode',
                'debug_enabled' => 'Enabled',
                'debug_disabled' => 'Disabled',
            ],
        ],
        'stats' => [
            'not_installed' => 'Not installed',
        ],
    ],
];
