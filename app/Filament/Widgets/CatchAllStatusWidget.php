<?php

namespace App\Filament\Widgets;

use App\Settings\CatchAllSettings;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CatchAllStatusWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected ?string $heading = 'Scheduled Sorting';

    protected function getStats(): array
    {
        $enabled = app(CatchAllSettings::class)->enabled;

        return [
            Stat::make('Status', $enabled ? 'Active' : 'Inactive')
                ->color($enabled ? 'success' : 'danger'),
        ];
    }
}
