<?php

use App\Models\User;

it('can render the catchall settings page', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/manage-catch-all-settings')
        ->assertOk();
});
