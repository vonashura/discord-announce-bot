<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Vercel: create writable storage dirs in /tmp
if (isset($_ENV['VERCEL'])) {
    $tmpStorage = '/tmp/laravel-storage';
    foreach ([
        'framework/cache/data',
        'framework/sessions',
        'framework/views',
        'logs',
        'app',
    ] as $dir) {
        $path = "$tmpStorage/$dir";
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }
}

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

// Remap storage to /tmp on Vercel (filesystem is read-only)
if (isset($_ENV['VERCEL'])) {
    $app->useStoragePath('/tmp/laravel-storage');
}


$app->handleRequest(Request::capture());
