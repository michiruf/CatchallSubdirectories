<?php

use App\Filament\Auth\SingleUserLogin;
use App\Models\User;
use Livewire\Livewire;

it('can render the single user login page', function () {
    $this->get('/admin/login')
        ->assertOk();
});

it('creates a user on first visit and authenticates with env password', function () {
    expect(User::count())->toBe(0);

    $this->get('/admin/login');

    expect(User::count())->toBe(1);

    Livewire::test(SingleUserLogin::class)
        ->fillForm([
            'password' => config('catchall.single_user_password'),
            'remember' => false,
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors();

    $this->assertAuthenticated();
});

it('rejects wrong password', function () {
    Livewire::test(SingleUserLogin::class)
        ->fillForm([
            'password' => 'wrong-password',
            'remember' => false,
        ])
        ->call('authenticate')
        ->assertHasFormErrors(['password']);

    $this->assertGuest();
});
