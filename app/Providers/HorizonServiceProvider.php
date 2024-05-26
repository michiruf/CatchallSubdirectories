<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Http\Request;
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

    protected function authorization(): void
    {
        $this->gate();

        Horizon::auth(function (Request $request) {
            $access = $request->has('ok')
                || $request->cookie('viewHorizon', 'false') === 'true'
                || Gate::check('viewHorizon', [$request->user()])
                || app()->environment('local');

            if ($access) {
                Cookie::queue('viewHorizon', 'true', 7 * 24 * 60);
            }

            return $access;
        });
    }

    protected function gate(): void
    {
        Gate::define('viewHorizon', function (User $user) {
            return in_array($user->email, [
                //
            ]);
        });
    }
}
