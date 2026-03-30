<?php

namespace App\Filament\Auth;

use App\Models\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use SensitiveParameter;

class SingleUserProvider extends EloquentUserProvider
{
    public const string EMAIL = 'single-user@localhost';

    public function retrieveByCredentials(#[SensitiveParameter] array $credentials): User|(Model&UserContract)|null
    {
        return User::updateOrCreate(
            ['email' => SingleUserProvider::EMAIL],
            ['name' => 'App', 'password' => Str::password()],
        );
    }

    public function validateCredentials(UserContract $user, #[SensitiveParameter] array $credentials): bool
    {
        return hash_equals(config('app.single_user_password'), $credentials['password']);
    }

    public function rehashPasswordIfRequired(UserContract $user, #[SensitiveParameter] array $credentials, bool $force = false)
    {
        //
    }
}
