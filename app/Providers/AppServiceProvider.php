<?php

namespace App\Providers;

use App\Imap\LazyInitializedConnection;
use Ddeboer\Imap\ConnectionInterface;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ConnectionInterface::class, LazyInitializedConnection::class);
    }

    public function boot(): void
    {
        // Force usage of https when loading vite resources on a production or staging server
        $isProduction = config('app.env') === 'production';
        $isStaging = config('app.env') === 'staging' || str(config('app.url'))->contains('staging');
        if ($isProduction || $isStaging) {
            URL::forceScheme('https');

            // Set that the request was made via https, because else signatures cannot get validated
            request()->server->set('HTTPS', request()->header('X-Forwarded-Proto', 'https') == 'https' ? 'on' : 'off');
        }
    }
}
