<?php

namespace App\Filament\Pages;

use App\Settings\CatchAllSettings;
use BackedEnum;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageCatchAllSettings extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string $settings = CatchAllSettings::class;

    protected static ?string $title = 'Catchall Settings';

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('enabled')
                    ->label('Scheduled sorting enabled')
                    ->helperText('When enabled, incoming mails are automatically sorted into subdirectories every 5 minutes.'),
            ]);
    }
}
