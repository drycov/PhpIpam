<?php

return [
    'phpipam' => [
        'url' => env('PHPIPAM_URL', 'https://ipam.local/api'),
        'token' => env('PHPIPAM_TOKEN', ''),
        'username' => env('PHPIPAM_USERNAME', 'admin'),
        'password' => env('PHPIPAM_PASSWORD', ''),
        'app_id' => env('PHPIPAM_APP_ID', 'librenms'),
        'verify_ssl' => env('PHPIPAM_VERIFY_SSL', true),
        'sync_interval' => env('PHPIPAM_SYNC_INTERVAL', 3600), // в секундах
    ],
];
