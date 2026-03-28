<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Foundation\Application;

class AppEnvironmentWidget extends StatsOverviewWidget
{
    protected ?string $heading = 'Application';

    protected function getStats(): array
    {
        return [
            Stat::make('App Name', config('app.name')),
            Stat::make('Environment', app()->environment()),
            Stat::make('PHP Version', PHP_VERSION),
            Stat::make('Laravel Version', Application::VERSION),
        ];
    }
}
