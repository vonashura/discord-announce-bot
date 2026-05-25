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

// Auto-migrate on Vercel (runs once per cold start, uses file lock in /tmp)
if (isset($_ENV['VERCEL'])) {
    $lockFile = '/tmp/laravel-migrated';
    if (!file_exists($lockFile)) {
        try {
            $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
            $kernel->call('migrate', ['--force' => true]);
            file_put_contents($lockFile, '1');
        } catch (\Throwable $e) {
            // ignore — DB might not be configured yet
        }
    }
}

$app->handleRequest(Request::capture());
