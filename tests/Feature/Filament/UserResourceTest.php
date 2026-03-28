<?php

use App\Models\User;

beforeEach(function () {
    config()->set('catchall.single_user_mode', false);
});

it('can render the user list page', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/users')
        ->assertOk();
});

it('can render the user create page', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/users/create')
        ->assertOk();
});

it('can render the user edit page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get("/admin/users/{$user->id}/edit")
        ->assertOk();
});

it('is hidden in single user mode', function () {
    config()->set('catchall.single_user_mode', true);

    $this->actingAs(User::factory()->create())
        ->get('/admin/users')
        ->assertForbidden();
});
