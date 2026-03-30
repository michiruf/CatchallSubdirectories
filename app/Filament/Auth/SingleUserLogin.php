<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\ValidationException;
use SensitiveParameter;

class SingleUserLogin extends Login
{
    public function boot(): void
    {
        $this->injectSingleUserProvider();
    }

    public function form(Schema $schema): Schema
    {
        $passwordComponent = $this->getPasswordFormComponent();

        return $schema->components([
            ($passwordComponent instanceof TextInput) ? $passwordComponent->autofocus() : $passwordComponent,
            $this->getRememberFormComponent(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(#[SensitiveParameter] array $data): array
    {
        return [
            'email' => SingleUserProvider::EMAIL,
            'password' => $data['password'],
        ];
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.password' => __('filament-panels::auth/pages/login.messages.failed'),
        ]);
    }

    private function injectSingleUserProvider(): void
    {
        $guard = Filament::auth();
        $guard->setProvider(new SingleUserProvider($guard->getProvider()->getHasher(), $guard->getProvider()->getModel()));
    }
}
