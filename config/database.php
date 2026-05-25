<?php

return [
    // Auto-detect: if DATABASE_URL is set → pgsql (Vercel/Neon), otherwise → sqlite (local)
    'default' => env('DATABASE_URL') ? 'pgsql' : env('DB_CONNECTION', 'sqlite'),

    'connections' => [
        'sqlite' => [
            'driver'                  => 'sqlite',
            'database'                => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix'                  => '',
            'foreign_key_constraints' => true,
        ],

        'pgsql' => [
            'driver'         => 'pgsql',
            'url'            => env('DATABASE_URL'),
            'host'           => env('DB_HOST', '127.0.0.1'),
            'port'           => env('DB_PORT', '5432'),
            'database'       => env('DB_DATABASE', 'laravel'),
            'username'       => env('DB_USERNAME', 'root'),
            'password'       => env('DB_PASSWORD', ''),
            'charset'        => 'utf8',
            'prefix'         => '',
            'prefix_indexes' => true,
            'search_path'    => 'public',
            'sslmode'        => 'require',
        ],
    ],

    'migrations' => [
        'table'                => 'migrations',
        'update_date_on_publish' => true,
    ],
];
