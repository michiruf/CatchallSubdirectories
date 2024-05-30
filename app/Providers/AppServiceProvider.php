<?php

namespace App\Providers;

use App\Imap\LazyInitializedConnection;
use Ddeboer\Imap\ConnectionInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ConnectionInterface::class, LazyInitializedConnection::class);
    }

    public function boot(): void
    {
        //
    }
}
