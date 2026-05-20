<?php

use App\Models\Vendor;

it('shows only the methods the vendor accepts', function () {
    $vendor = Vendor::factory()->live()->withPromptPay()->create(['slug' => 'mae-som']);

    $response = $this->get("/{$vendor->slug}/pay");

    $response->assertOk()
        ->assertSee('Pay '.$vendor->name)
        ->assertSee("setTab('promptpay')", escape: false)
        ->assertDontSee("setTab('card')", escape: false)
        ->assertDontSee("setTab('crypto')", escape: false);
});

it('renders all three tabs when vendor accepts all', function () {
    $vendor = Vendor::factory()->live()->withStripeConnect()->withPromptPay()->create([
        'stablecoin_address' => '0xabc123',
        'stablecoin_chain' => 'ETH',
    ]);

    $this->get("/{$vendor->slug}/pay")
        ->assertOk()
        ->assertSee("setTab('card')", escape: false)
        ->assertSee("setTab('promptpay')", escape: false)
        ->assertSee("setTab('crypto')", escape: false);
});

it('returns 404 for draft vendors', function () {
    $vendor = Vendor::factory()->create(['status' => 'draft']);

    $this->get("/{$vendor->slug}/pay")->assertNotFound();
});

it('returns 404 for unknown vendor slug', function () {
    $this->get('/does-not-exist/pay')->assertNotFound();
});

it('renders the PromptPay EMV payload in the visible markup', function () {
    $vendor = Vendor::factory()->live()->withPromptPay()->create([
        'promptpay_phone' => '0812345678',
    ]);

    $response = $this->get("/{$vendor->slug}/pay");

    $response->assertOk()
        ->assertSee('0066812345678')                       // phone with country code
        ->assertSee('A000000677010111')                    // PromptPay AID
        ->assertSee('data-test="promptpay-qr"', escape: false)
        ->assertSee('<svg', escape: false);                // rendered QR
});

it('renders the crypto address with copy button and chain badge', function () {
    $vendor = Vendor::factory()->live()->create([
        'stablecoin_address' => '0x742d35Cc6634C0532925a3b844Bc454e4438f44e',
        'stablecoin_chain' => 'POL',
    ]);

    $response = $this->get("/{$vendor->slug}/pay");

    $response->assertOk()
        ->assertSee('0x742d35Cc6634C0532925a3b844Bc454e4438f44e')
        ->assertSee('Polygon')                              // chain label
        ->assertSee('data-test="crypto-copy"', escape: false)
        ->assertSee('Adresse kopieren');
});

it('falls back to a helpful message when no payment method is configured', function () {
    $vendor = Vendor::factory()->live()->create();

    $this->get("/{$vendor->slug}/pay")
        ->assertOk()
        ->assertSee("hasn't set up any payment methods", escape: false);
});
