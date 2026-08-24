<?php

return [
    'navigation' => [
        'label' => 'Systeemversies',
        'group' => 'Instellingen',
    ],
    'actions' => [
        'check_now' => [
            'label' => 'Nu controleren',
            'modal_heading' => 'Dependencyversies nu controleren?',
            'modal_description' => 'Hiermee worden de geconfigureerde Composer-repositories gecontroleerd en wordt de opgeslagen dependency-snapshot vernieuwd.',
            'modal_submit' => 'Nu controleren',
            'success_title' => 'Dependencyversies bijgewerkt',
            'success_body' => 'De package-informatie op deze pagina is nu actueel.',
            'already_running_title' => 'Controle wordt al uitgevoerd',
            'already_running_body' => 'Er wordt al een dependencycontrole uitgevoerd. Probeer het over een paar minuten opnieuw.',
            'failure_title' => 'Dependencyversies konden niet worden gecontroleerd',
            'failure_body' => 'De bestaande snapshot is behouden. Controleer de applicatielogs en probeer het opnieuw.',
        ],
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
