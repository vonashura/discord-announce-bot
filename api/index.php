<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Serve /build/* assets before Laravel boots (avoids PHP route slash-matching issues)
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (preg_match('#^/build/(.+)$#', $uri, $m)) {
    $file = __DIR__ . '/../public/build/' . $m[1];
    if (is_file($file)) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $mime = match($ext) {
            'css'   => 'text/css',
            'js'    => 'application/javascript',
            'json'  => 'application/json',
            'woff2' => 'font/woff2',
            'woff'  => 'font/woff',
            'svg'   => 'image/svg+xml',
            default => 'application/octet-stream',
        };
        header("Content-Type: $mime");
        header("Cache-Control: public, immutable, max-age=31536000");
        readfile($file);
        exit;
    }
}

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
