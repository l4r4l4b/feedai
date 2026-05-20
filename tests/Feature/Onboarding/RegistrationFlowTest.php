<?php

use App\Models\OnboardingSession;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('vendors');
});

it('creates user, vendor and onboarding session atomically on register', function () {
    $response = $this->post('/register', [
        'name' => 'Mae Som',
        'email' => 'mae@example.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'locale' => 'th',
    ]);

    $response->assertRedirect();

    $user = User::where('email', 'mae@example.test')->first();
    expect($user)->not->toBeNull()
        ->and($user->role)->toBe('vendor');

    $vendor = $user->vendor;
    expect($vendor)->toBeInstanceOf(Vendor::class)
        ->and($vendor->status)->toBe('draft')
        ->and($vendor->locale)->toBe('th')
        ->and($vendor->slug)->toStartWith('mae-som-');

    expect(OnboardingSession::where('vendor_id', $vendor->id)->where('status', 'in_progress')->exists())
        ->toBeTrue();

    Storage::disk('vendors')->assertExists($vendor->slug.'/vendor.yaml');
    Storage::disk('vendors')->assertExists($vendor->slug.'/pages/home.yaml');
});

it('generates a fallback slug for purely non-ASCII names', function () {
    $this->post('/register', [
        'name' => 'แม่สม',
        'email' => 'thai@example.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'locale' => 'th',
    ])->assertRedirect();

    $vendor = User::where('email', 'thai@example.test')->first()->vendor;

    expect($vendor->slug)->toStartWith('vendor-');
});
