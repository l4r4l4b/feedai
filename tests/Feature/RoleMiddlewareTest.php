<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    // Test-only routes so we never depend on application routes
    // (which may evolve as later phases land).
    Route::middleware(['web', 'auth', 'vendor'])->get('/__test/vendor-area', fn () => 'vendor ok');
    Route::middleware(['web', 'auth', 'admin'])->get('/__test/admin-area', fn () => 'admin ok');
});

it('blocks guests from vendor area', function () {
    // Unauthenticated → redirected to login by auth middleware,
    // EnsureVendor never gets to assert role.
    $this->get('/__test/vendor-area')->assertRedirect('/login');
});

it('blocks guests from admin area', function () {
    $this->get('/__test/admin-area')->assertRedirect('/login');
});

it('allows vendor users into vendor area', function () {
    $vendor = User::factory()->vendor()->create();

    $this->actingAs($vendor)
        ->get('/__test/vendor-area')
        ->assertOk()
        ->assertSee('vendor ok');
});

it('blocks vendor users from admin area with 403', function () {
    $vendor = User::factory()->vendor()->create();

    $this->actingAs($vendor)
        ->get('/__test/admin-area')
        ->assertForbidden();
});

it('allows admin users into admin area', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/__test/admin-area')
        ->assertOk()
        ->assertSee('admin ok');
});

it('blocks admin users from vendor area with 403', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/__test/vendor-area')
        ->assertForbidden();
});
