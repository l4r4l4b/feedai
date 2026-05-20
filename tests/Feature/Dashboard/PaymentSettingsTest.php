<?php

use App\Livewire\Dashboard\Payments\Settings;
use App\Models\User;
use App\Models\Vendor;
use Livewire\Livewire;

it('redirects guests to login', function () {
    $this->get('/dashboard/payments')->assertRedirect('/login');
});

it('shows three sections for the authenticated vendor', function () {
    $user = User::factory()->vendor()->create();
    Vendor::factory()->for($user)->create();

    $this->actingAs($user)->get('/dashboard/payments')
        ->assertOk()
        ->assertSee('Credit card')
        ->assertSee('PromptPay')
        ->assertSee('Crypto');
});

it('marks PromptPay as active once a phone is saved', function () {
    $user = User::factory()->vendor()->create();
    $vendor = Vendor::factory()->for($user)->withPromptPay()->create();

    Livewire::actingAs($user)
        ->test(Settings::class)
        ->assertSet('promptpayPhone', $vendor->promptpay_phone)
        ->assertSee('Active');
});

it('saves direct payment fields and persists them on the vendor', function () {
    $user = User::factory()->vendor()->create();
    $vendor = Vendor::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(Settings::class)
        ->set('promptpayPhone', '0812345678')
        ->set('stablecoinAddress', '0xabc')
        ->set('stablecoinChain', 'ETH')
        ->call('saveDirectPayments');

    expect($vendor->fresh())
        ->promptpay_phone->toBe('0812345678')
        ->stablecoin_address->toBe('0xabc')
        ->stablecoin_chain->toBe('ETH');
});

it('rejects invalid stablecoin chain values', function () {
    $user = User::factory()->vendor()->create();
    Vendor::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(Settings::class)
        ->set('stablecoinChain', 'XRPGOLD')
        ->call('saveDirectPayments')
        ->assertHasErrors(['stablecoinChain']);
});

it('shows the active badge for Stripe once charges are enabled', function () {
    $user = User::factory()->vendor()->create();
    Vendor::factory()->for($user)->withStripeConnect()->create();

    $this->actingAs($user)->get('/dashboard/payments')
        ->assertOk()
        ->assertSeeInOrder(['Credit card', 'Active']);
});

it('instantly activates Stripe in demo mode (no real API call)', function () {
    config()->set('services.stripe.demo_mode', true);

    $user = User::factory()->vendor()->create();
    $vendor = Vendor::factory()->for($user)->create([
        'stripe_account_id' => null,
        'stripe_charges_enabled' => false,
        'stripe_details_submitted' => false,
    ]);

    Livewire::actingAs($user)
        ->test(Settings::class)
        ->call('startStripeOnboarding');

    expect($vendor->fresh())
        ->stripe_account_id->toStartWith('acct_demo_')
        ->stripe_charges_enabled->toBeTrue()
        ->stripe_details_submitted->toBeTrue();
});
