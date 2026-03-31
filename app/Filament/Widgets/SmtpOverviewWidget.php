<?php

namespace App\Filament\Widgets;

use App\Settings\CatchAllSettings;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SmtpOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'IMAP Connection';

    protected function getStats(): array
    {
        $settings = app(CatchAllSettings::class);

        return [
            Stat::make('Hostname', $settings->hostname()),
            Stat::make('Port', $settings->port()),
            Stat::make('Inbox', $settings->inboxName()),
            Stat::make('Mail Domain', $settings->mailDomain()),
        ];
    }
}
