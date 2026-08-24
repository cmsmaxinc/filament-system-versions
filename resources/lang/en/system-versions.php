<?php

return [
    'navigation' => [
        'label' => 'System Versions',
        'group' => 'Settings',
    ],
    'actions' => [
        'check_now' => [
            'label' => 'Check now',
            'modal_heading' => 'Check dependency versions now?',
            'modal_description' => 'This contacts the configured Composer repositories and refreshes the stored dependency snapshot.',
            'modal_submit' => 'Check now',
            'success_title' => 'Dependency versions updated',
            'success_body' => 'The package information on this page is now current.',
            'already_running_title' => 'Check already running',
            'already_running_body' => 'Another dependency version check is in progress. Try again in a few minutes.',
            'failure_title' => 'Unable to check dependency versions',
            'failure_body' => 'The existing snapshot was kept. Review the application logs and try again.',
        ],
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
