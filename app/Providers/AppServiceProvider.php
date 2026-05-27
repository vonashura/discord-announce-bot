<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Vercel terminates TLS at the edge and forwards HTTP internally.
        // Force HTTPS scheme so all generated URLs (form actions, redirects)
        // use https:// and avoid browser mixed-content / "not secure" warnings.
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
