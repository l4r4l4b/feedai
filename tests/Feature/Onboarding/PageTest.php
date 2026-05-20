<?php

use App\Livewire\Onboarding\Page;
use App\Models\OnboardingSession;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('renders the onboarding split view for draft vendors', function () {
    $vendor = Vendor::factory()->create(['status' => 'draft', 'slug' => 'demo-shop']);
    OnboardingSession::factory()->create(['vendor_id' => $vendor->id]);

    Livewire::actingAs($vendor->user)
        ->test(Page::class)
        ->assertOk()
        ->assertSee('Live Preview', escape: false)
        ->assertSee('FeedAI Onboarding', escape: false)
        ->assertSee('/demo-shop', escape: false);
});

it('redirects to dashboard if vendor is already live', function () {
    $vendor = Vendor::factory()->live()->create();

    Livewire::actingAs($vendor->user)
        ->test(Page::class)
        ->assertRedirect(route('dashboard'));
});

it('aborts with 404 if user has no vendor', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('onboarding'))
        ->assertNotFound();
});
