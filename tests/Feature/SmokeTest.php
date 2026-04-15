<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

test('dashboard loads successfully', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get('/dashboard')
        ->assertStatus(200)
        ->assertSee('Client Dashboard');
});

test('admin dashboard loads successfully', function () {
    $user = User::factory()->create(['role' => 'admin']);

    actingAs($user)
        ->get('/admin')
        ->assertStatus(200)
        ->assertSee('System Control Center');
});
