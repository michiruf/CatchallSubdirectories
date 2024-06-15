<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class PulseAuthorizationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define('viewPulse', function (?User $user) {
            /** @var Request $request */
            $request = request();
            $access = $request->has('ok') || $request->cookie('viewPulse', 'false') === 'true';
            if ($access) {
                Cookie::queue('viewPulse', 'true', 7 * 24 * 60);

                return true;
            }

            return $user && in_array($user->email, [
                //
            ]);
        });
    }
}
