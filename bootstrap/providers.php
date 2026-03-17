<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\HealthServiceProvider;
use App\Providers\HorizonServiceProvider;
use App\Providers\PulseAuthorizationServiceProvider;
use App\Providers\TelescopeServiceProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    HealthServiceProvider::class,
    HorizonServiceProvider::class,
    PulseAuthorizationServiceProvider::class,
    TelescopeServiceProvider::class,
];
