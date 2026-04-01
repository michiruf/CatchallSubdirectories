<?php

namespace App\Filament\Pages;

use App\Jobs\CatchAllSubdirectories;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected function getHeaderActions(): array
    {
        return [
            Action::make('sortSubdirectories')
                ->label('Run Now')
                ->icon('heroicon-o-play')
                ->requiresConfirmation()
                ->modalDescription('Sort inbox mails into subdirectories based on the recipient prefix.')
                ->action(function () {
                    CatchAllSubdirectories::dispatchSync();

                    Notification::make()
                        ->title('Mails sorted into subdirectories')
                        ->success()
                        ->send();
                }),
        ];
    }
}
