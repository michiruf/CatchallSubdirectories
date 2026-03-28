<?php

use App\Models\User;

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
