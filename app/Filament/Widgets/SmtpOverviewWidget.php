<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SmtpOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;
    protected ?string $heading = 'IMAP Connection';

    protected function getStats(): array
    {
        return [
            Stat::make('Hostname', config('catchall.hostname')),
            Stat::make('Port', config('catchall.port')),
            Stat::make('Inbox', config('catchall.inbox_name')),
            Stat::make('Mail Domain', config('catchall.mail_domain')),
        ];
    }
}
