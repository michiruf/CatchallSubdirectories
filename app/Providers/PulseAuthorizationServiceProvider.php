<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class PulseAuthorizationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define('viewPulse', function (?User $user) {
            if (config('app.simple_gates') && request()->has('ok')) {
                Cookie::queue('viewPulse', 'true', 7 * 24 * 60);

                return true;
            }

            return $user !== null;
        });
    }
}
