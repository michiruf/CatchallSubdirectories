<?php

namespace App\Filament\Auth;

use App\Models\User;
use Filament\Auth\Pages\Login;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use SensitiveParameter;

class SingleUserLogin extends Login
{
    public function mount(): void
    {
        $this->ensureSingleUserExists();

        parent::mount();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ]);
    }

    protected function getPasswordFormComponent(): Component
    {
        return parent::getPasswordFormComponent()
            ->autofocus();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(#[SensitiveParameter] array $data): array
    {
        $user = User::first();

        return [
            'email' => $user->email,
            'password' => $data['password'],
        ];
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.password' => __('filament-panels::auth/pages/login.messages.failed'),
        ]);
    }

    private function ensureSingleUserExists(): void
    {
        $password = config('catchall.single_user_password');
        $user = User::first();

        if (! $user) {
            User::create([
                'name' => 'Admin',
                'email' => 'admin@localhost',
                'password' => $password,
            ]);

            return;
        }

        if (! Hash::check($password, $user->password)) {
            $user->update(['password' => $password]);
        }
    }
}
