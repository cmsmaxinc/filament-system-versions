<?php

return [
    'navigation' => [
        'label' => 'Systeemversies',
        'group' => 'Instellingen',
    ],
    'widgets' => [
        'dependency' => [
            'heading' => 'Dependencies',
            'description' => 'Een lijst met dependencies met beschikbare updates',
            'empty' => 'Geen dependencies met beschikbare updates',
            'abandoned' => 'Niet meer onderhouden',
            'no_data' => 'Nog geen dependency-gegevens. Voer het Artisan-commando dependency:versions uit om deze te verzamelen.',
            'missing_table' => 'De composer versions-tabel is nog niet gemigreerd. Publiceer en voer eerst de migraties van dit package uit.',
            'table' => [
                'name' => 'Naam',
                'version' => 'Versie',
            ],
        ],
        'system' => [
            'heading' => 'Systeemeigenschappen',
            'description' => 'Een samenvatting van de systeemomgeving',
            'details' => [
                'environment' => 'Omgeving',
                'timezone' => 'Tijdzone',
                'debug' => 'Debug-modus',
                'debug_enabled' => 'Ingeschakeld',
                'debug_disabled' => 'Uitgeschakeld',
            ],
        ],
        'stats' => [
            'not_installed' => 'Niet geïnstalleerd',
        ],
    ],
];
