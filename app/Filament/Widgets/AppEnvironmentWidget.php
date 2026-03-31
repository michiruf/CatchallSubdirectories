<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Foundation\Application;
use Laravel\Horizon\Contracts\MasterSupervisorRepository;

class AppEnvironmentWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'System';

    protected function getStats(): array
    {
        $masters = app(MasterSupervisorRepository::class)->all();
        $isRunning = count($masters) > 0;

        return [
            Stat::make('Environment', app()->environment()),
            Stat::make('PHP Version', PHP_VERSION),
            Stat::make('Laravel Version', Application::VERSION),
            Stat::make('Users', User::count()),
            Stat::make('Horizon', $isRunning ? 'Running' : 'Stopped')
                ->color($isRunning ? 'success' : 'danger'),
            Stat::make('Pending Jobs', app('queue')->size()),
        ];
    }
}
