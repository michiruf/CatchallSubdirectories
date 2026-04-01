<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\ManageCatchAllSettings;
use App\Settings\CatchAllSettings;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CatchAllOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Catchall';

    protected function getStats(): array
    {
        $settings = app(CatchAllSettings::class);

        return [
            Stat::make('Sorting', $settings->enabled ? 'Active' : 'Inactive')
                ->description($settings->enabled ? 'Schedule is running' : 'Schedule is paused')
                ->descriptionColor($settings->enabled ? 'success' : 'danger')
                ->url(ManageCatchAllSettings::getUrl()),
            Stat::make('Last Run', $settings->last_run_at?->diffForHumans() ?? 'Never'),
            Stat::make('Hostname', $settings->hostname()),
            Stat::make('Port', $settings->port()),
            Stat::make('Inbox', $settings->inboxName()),
            Stat::make('Mail Domain', $settings->mailDomain()),
        ];
    }
}
