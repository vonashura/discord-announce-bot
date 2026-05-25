<?php

return [
    'driver'      => env('SESSION_DRIVER', 'cookie'),
    'lifetime'    => env('SESSION_LIFETIME', 120),
    'encrypt'     => env('SESSION_ENCRYPT', false),
    'files'       => storage_path('framework/sessions'),
    'table'       => env('SESSION_TABLE', 'sessions'),
    'cookie'      => env('SESSION_COOKIE', 'discord_bot_session'),
    'path'        => '/',
    'domain'      => env('SESSION_DOMAIN'),
    'secure'      => env('SESSION_SECURE_COOKIE'),
    'http_only'   => env('SESSION_HTTP_ONLY', true),
    'same_site'   => env('SESSION_SAME_SITE', 'lax'),
    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),
];
