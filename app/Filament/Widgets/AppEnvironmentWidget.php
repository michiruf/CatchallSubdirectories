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

        $stats = [
            Stat::make('Environment', app()->environment()),
            Stat::make('PHP Version', PHP_VERSION),
            Stat::make('Laravel Version', Application::VERSION),
        ];

        if (! config('app.single_user_mode')) {
            $stats[] = Stat::make('Users', User::count());
        }

        $stats[] = Stat::make('Horizon', $isRunning ? 'Running' : 'Stopped')
            ->description($isRunning ? 'Workers are active' : 'No workers running')
            ->descriptionColor($isRunning ? 'success' : 'danger');
        $stats[] = Stat::make('Pending Jobs', app('queue')->size());

        return $stats;
    }
}
