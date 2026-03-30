<?php

use App\Filament\Auth\SingleUserLogin;
use App\Models\User;
use Livewire\Livewire;

it('can render the single user login page', function () {
    $this->get('/admin/login')
        ->assertOk();
});

it('creates a user on first login and authenticates with env password', function () {
    expect(User::count())->toBe(0);

    Livewire::test(SingleUserLogin::class)
        ->fillForm([
            'password' => config('catchall.single_user_password'),
            'remember' => false,
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors();

    expect()
        ->and(User::count())->toBe(1)
        ->and(User::first()->email)->toBe('single-user@localhost');

    $this->assertAuthenticated();
});

it('rate limits after too many attempts', function () {
    for ($i = 0; $i < 5; $i++) {
        Livewire::test(SingleUserLogin::class)
            ->fillForm(['password' => 'wrong-password', 'remember' => false])
            ->call('authenticate');
    }

    Livewire::test(SingleUserLogin::class)
        ->fillForm(['password' => 'wrong-password', 'remember' => false])
        ->call('authenticate')
        ->assertNotified();

    $this->assertGuest();
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
