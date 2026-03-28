<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Laravel\Horizon\Contracts\MasterSupervisorRepository;

class HorizonOverviewWidget extends StatsOverviewWidget
{
    protected ?string $heading = 'Horizon';

    protected function getStats(): array
    {
        $masters = app(MasterSupervisorRepository::class)->all();
        $isRunning = count($masters) > 0;

        $pendingJobs = app('queue')->size();

        return [
            Stat::make('Status', $isRunning ? 'Running' : 'Stopped')
                ->color($isRunning ? 'success' : 'danger'),
            Stat::make('Pending Jobs', $pendingJobs),
        ];
    }
}
