<?php

namespace App\Filament\Pages;

use App\Settings\CatchAllSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageCatchAllSettings extends SettingsPage
{
    private const string PASSWORD_PLACEHOLDER = '********';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string $settings = CatchAllSettings::class;

    protected static ?string $title = 'Catchall Settings';

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    private static function defaultHint(mixed $value): string
    {
        return $value ? 'Default: '.$value : 'Default not set';
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['password'] = filled($data['password']) ? self::PASSWORD_PLACEHOLDER : null;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (blank($data['password']) || $data['password'] === self::PASSWORD_PLACEHOLDER) {
            unset($data['password']);
        }

        return $data;
    }

    public function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
            Action::make('clearPasswordOverride')
                ->label('Clear Password Override')
                ->icon('heroicon-o-key')
                ->color('gray')
                ->requiresConfirmation()
                ->modalDescription('This will remove the password override and fall back to the .env value.')
                ->visible(fn () => filled(app(CatchAllSettings::class)->password))
                ->action(function () {
                    $settings = app(CatchAllSettings::class);
                    $settings->password = null;
                    $settings->save();

                    $this->fillForm();

                    Notification::make()
                        ->title('Password override cleared')
                        ->success()
                        ->send();
                }),
        ];
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
                            ->live()
                            ->revealable(fn (callable $get): bool => $get('password') !== self::PASSWORD_PLACEHOLDER)
                            ->hint(app(CatchAllSettings::class)->password ? 'Override is set' : (config('catchall.password') ? 'Default set in .env' : 'Default not set')),
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
