<?php

use App\Models\Payment;
use App\Models\Vendor;
use Stripe\WebhookSignature;

/**
 * Builds a payload + valid Stripe-Signature header for the given secret.
 * Mirrors Stripe's signing scheme so we exercise real signature verification.
 *
 * @return array{0: string, 1: string}
 */
function signedStripePayload(array $event, string $secret): array
{
    $payload = json_encode($event, JSON_THROW_ON_ERROR);
    $timestamp = time();
    $signedPayload = $timestamp.'.'.$payload;
    $signature = hash_hmac('sha256', $signedPayload, $secret);

    return [$payload, 't='.$timestamp.',v1='.$signature];
}

beforeEach(function () {
    config(['services.stripe.webhook_secret' => 'whsec_test_secret']);
});

it('marks a payment as paid on checkout.session.completed', function () {
    $payment = Payment::factory()->for(Vendor::factory())->create(['status' => 'pending']);

    $event = [
        'id' => 'evt_'.fake()->uuid(),
        'type' => 'checkout.session.completed',
        'data' => ['object' => [
            'object' => 'checkout.session',
            'id' => 'cs_test_paid',
            'payment_intent' => 'pi_test_123',
            'metadata' => ['payment_id' => (string) $payment->id],
        ]],
    ];

    [$payload, $sig] = signedStripePayload($event, 'whsec_test_secret');

    $this->call('POST', '/stripe/webhook', [], [], [], [
        'HTTP_STRIPE_SIGNATURE' => $sig,
        'CONTENT_TYPE' => 'application/json',
    ], $payload)->assertOk();

    expect($payment->fresh())
        ->status->toBe('paid')
        ->paid_at->not->toBeNull()
        ->provider_reference->toBe('pi_test_123');
});

it('marks a payment as failed on payment_intent.payment_failed', function () {
    $payment = Payment::factory()->for(Vendor::factory())->create(['status' => 'pending']);

    $event = [
        'id' => 'evt_'.fake()->uuid(),
        'type' => 'payment_intent.payment_failed',
        'data' => ['object' => [
            'object' => 'payment_intent',
            'id' => 'pi_test_fail',
            'metadata' => ['payment_id' => (string) $payment->id],
        ]],
    ];

    [$payload, $sig] = signedStripePayload($event, 'whsec_test_secret');

    $this->call('POST', '/stripe/webhook', [], [], [], [
        'HTTP_STRIPE_SIGNATURE' => $sig,
        'CONTENT_TYPE' => 'application/json',
    ], $payload)->assertOk();

    expect($payment->fresh()->status)->toBe('failed');
});

it('rejects requests with a missing or invalid signature with 400', function () {
    $event = [
        'id' => 'evt_x',
        'type' => 'checkout.session.completed',
        'data' => ['object' => ['object' => 'checkout.session', 'id' => 'cs_x', 'metadata' => []]],
    ];

    $payload = json_encode($event);

    // No signature header
    $this->call('POST', '/stripe/webhook', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
    ], $payload)->assertStatus(400);

    // Wrong secret
    [$payloadSigned, $sig] = signedStripePayload($event, 'whsec_wrong_secret');
    $this->call('POST', '/stripe/webhook', [], [], [], [
        'HTTP_STRIPE_SIGNATURE' => $sig,
        'CONTENT_TYPE' => 'application/json',
    ], $payloadSigned)->assertStatus(400);
});

it('returns 500 if webhook secret is not configured', function () {
    config(['services.stripe.webhook_secret' => null]);

    $this->call('POST', '/stripe/webhook', [], [], [], [
        'HTTP_STRIPE_SIGNATURE' => 't=1,v1=x',
        'CONTENT_TYPE' => 'application/json',
    ], '{}')->assertStatus(500);
});

it('acknowledges unknown event types without side effects', function () {
    $event = [
        'id' => 'evt_unknown',
        'type' => 'customer.created',
        'data' => ['object' => ['id' => 'cus_x']],
    ];

    [$payload, $sig] = signedStripePayload($event, 'whsec_test_secret');

    $this->call('POST', '/stripe/webhook', [], [], [], [
        'HTTP_STRIPE_SIGNATURE' => $sig,
        'CONTENT_TYPE' => 'application/json',
    ], $payload)->assertOk();
});

it('exempts the webhook route from CSRF protection', function () {
    // Posting JSON without CSRF token must NOT be rejected by 419.
    // (The webhook will still reject as 400 because no signature, but never 419.)
    $this->call('POST', '/stripe/webhook', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
    ], '{}')->assertStatus(400);
});

it('syncs vendor Connect status on account.updated', function () {
    $vendor = Vendor::factory()->create([
        'stripe_account_id' => 'acct_test_xyz',
        'stripe_charges_enabled' => false,
        'stripe_details_submitted' => false,
    ]);

    $event = [
        'id' => 'evt_'.fake()->uuid(),
        'type' => 'account.updated',
        'data' => ['object' => [
            'object' => 'account',
            'id' => 'acct_test_xyz',
            'charges_enabled' => true,
            'details_submitted' => true,
        ]],
    ];

    [$payload, $sig] = signedStripePayload($event, 'whsec_test_secret');

    $this->call('POST', '/stripe/webhook', [], [], [], [
        'HTTP_STRIPE_SIGNATURE' => $sig,
        'CONTENT_TYPE' => 'application/json',
    ], $payload)->assertOk();

    expect($vendor->fresh())
        ->stripe_charges_enabled->toBeTrue()
        ->stripe_details_submitted->toBeTrue();
});

it('ignores account.updated for an unknown stripe account', function () {
    $event = [
        'id' => 'evt_'.fake()->uuid(),
        'type' => 'account.updated',
        'data' => ['object' => [
            'object' => 'account',
            'id' => 'acct_unknown_999',
            'charges_enabled' => true,
            'details_submitted' => true,
        ]],
    ];

    [$payload, $sig] = signedStripePayload($event, 'whsec_test_secret');

    $this->call('POST', '/stripe/webhook', [], [], [], [
        'HTTP_STRIPE_SIGNATURE' => $sig,
        'CONTENT_TYPE' => 'application/json',
    ], $payload)->assertOk();
});

afterEach(function () {
    WebhookSignature::class; // ensure class is loaded for autoload
});
