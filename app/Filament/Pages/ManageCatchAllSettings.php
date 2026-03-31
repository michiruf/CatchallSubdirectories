<?php

namespace App\Filament\Pages;

use App\Settings\CatchAllSettings;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageCatchAllSettings extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string $settings = CatchAllSettings::class;

    protected static ?string $title = 'Catchall Settings';

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    private static function defaultHint(mixed $value): string
    {
        return $value ? 'Default: '.$value : 'Default not set';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General')
                    ->columnSpanFull()
                    ->schema([
                        Toggle::make('enabled')
                            ->label('Scheduled sorting enabled')
                            ->helperText('When enabled, incoming mails are automatically sorted into subdirectories every 5 minutes.'),
                        Toggle::make('subscribe_new_folders')
                            ->label('Subscribe to new folders')
                            ->helperText('Automatically subscribe to newly created subdirectories so they appear in your mail client.'),
                    ]),
                Section::make('Connection Overrides')
                    ->columnSpanFull()
                    ->columns(2)
                    ->description('Leave empty to use the value from the .env file.')
                    ->schema([
                        TextInput::make('hostname')
                            ->label('Hostname')
                            ->hint(self::defaultHint(config('catchall.hostname'))),
                        TextInput::make('port')
                            ->label('Port')
                            ->numeric()
                            ->hint(self::defaultHint(config('catchall.port'))),
                        TextInput::make('username')
                            ->label('Username')
                            ->hint(self::defaultHint(config('catchall.username'))),
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->hint(config('catchall.password') ? 'Default set in .env' : 'Default not set'),
                        TextInput::make('inbox_name')
                            ->label('Inbox Name')
                            ->hint(self::defaultHint(config('catchall.inbox_name'))),
                        TextInput::make('mail_domain')
                            ->label('Mail Domain')
                            ->hint(self::defaultHint(config('catchall.mail_domain'))),
                        Select::make('validate_cert')
                            ->label('Validate Certificate')
                            ->options([
                                true => 'Yes',
                                false => 'No',
                            ])
                            ->placeholder('Use default')
                            ->hint(config('catchall.validate_cert') !== null
                                ? 'Default: '.(config('catchall.validate_cert') ? 'Yes' : 'No')
                                : 'Default not set'),
                    ]),
            ]);
    }
}
