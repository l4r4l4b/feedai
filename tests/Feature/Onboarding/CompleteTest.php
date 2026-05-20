<?php

use App\Livewire\Onboarding\Complete;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('renders the three next-step cards for a freshly live vendor', function () {
    $vendor = Vendor::factory()->live()->create([
        'slug' => 'mae-som',
        'name' => 'Mae Som Kitchen',
    ]);

    $this->actingAs($vendor->user)
        ->get(route('onboarding.complete'))
        ->assertOk()
        ->assertSee('Feed is live', escape: false)
        ->assertSee('Mae Som Kitchen', escape: false)
        ->assertSee('Set up payment methods', escape: false)
        ->assertSee('Test the inbox', escape: false)
        ->assertSee('Share the feed', escape: false);
});

it('redirects back to onboarding when vendor is not live yet', function () {
    $vendor = Vendor::factory()->create(['status' => 'draft']);

    Livewire::actingAs($vendor->user)
        ->test(Complete::class)
        ->assertRedirect(route('onboarding'));
});

it('redirects admin users to admin dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Livewire::actingAs($admin)
        ->test(Complete::class)
        ->assertRedirect(route('admin.dashboard'));
});

it('aborts 404 for users without a vendor', function () {
    $user = User::factory()->create(['role' => 'vendor']);

    $this->actingAs($user)
        ->get(route('onboarding.complete'))
        ->assertNotFound();
});

it('requires authentication', function () {
    $this->get(route('onboarding.complete'))
        ->assertRedirect(route('login'));
});
