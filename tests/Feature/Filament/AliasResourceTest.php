<?php

use App\Models\Alias;
use App\Models\User;

it('can render the alias list page', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/aliases')
        ->assertOk();
});

it('can render the alias create page', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/aliases/create')
        ->assertOk();
});

it('can render the alias edit page', function () {
    $alias = Alias::create([
        'source_prefix' => 'test',
        'destination_prefix' => 'inbox',
    ]);

    $this->actingAs(User::factory()->create())
        ->get("/admin/aliases/{$alias->id}/edit")
        ->assertOk();
});
