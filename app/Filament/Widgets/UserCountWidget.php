<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserCountWidget extends StatsOverviewWidget
{
    protected ?string $heading = 'Users';

    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count()),
        ];
    }
}
