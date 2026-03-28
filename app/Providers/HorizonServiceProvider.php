<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    public function boot(): void
    {
        parent::boot();

        // Horizon::routeSmsNotificationsTo('15556667777');
        // Horizon::routeMailNotificationsTo('example@example.com');
        // Horizon::routeSlackNotificationsTo('slack-webhook-url', '#channel');
    }

    protected function gate(): void
    {
        Gate::define('viewHorizon', function (?User $user) {
            if (config('app.simple_gates') && request()->has('ok')) {
                Cookie::queue('viewHorizon', 'true', 7 * 24 * 60);

                return true;
            }

            return $user !== null;
        });
    }
}
