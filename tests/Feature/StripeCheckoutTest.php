<?php

use App\Models\Payment;
use App\Models\Vendor;
use App\Services\StripeCheckout;
use Stripe\Checkout\Session;
use Stripe\Service\Checkout\CheckoutServiceFactory;
use Stripe\Service\Checkout\SessionService;
use Stripe\StripeClient;

// These tests verify the real Stripe call path. Demo mode short-circuits
// that, so disable it here — dedicated demo cases live further down.
beforeEach(function () {
    config()->set('services.stripe.demo_mode', false);
});

function stripeSessionStub(string $id, string $url): Session
{
    // Stripe blocks direct `id` assignment on StripeObject — use constructFrom.
    return Session::constructFrom(['id' => $id, 'url' => $url, 'object' => 'checkout.session']);
}

function mockStripeClient(string $sessionId, string $url): array
{
    $captured = new stdClass;
    $captured->params = null;

    $sessions = Mockery::mock(SessionService::class);
    $checkout = Mockery::mock(CheckoutServiceFactory::class);
    $client = Mockery::mock(StripeClient::class);

    $checkout->sessions = $sessions;
    $client->checkout = $checkout;

    $sessions->shouldReceive('create')
        ->once()
        ->andReturnUsing(function (array $params) use ($sessionId, $url, $captured) {
            $captured->params = $params;

            return stripeSessionStub($sessionId, $url);
        });

    return [$client, $captured];
}

it('creates a pending payment and stripe session for a fixed amount', function () {
    [$client, $captured] = mockStripeClient('cs_test_fixed_1', 'https://checkout.stripe.com/cs_test_fixed_1');
    $service = new StripeCheckout($client);
    $vendor = Vendor::factory()->withStripeConnect()->create();

    $result = $service->fixedAmount(
        $vendor,
        amountCents: 120000,
        currency: 'THB',
        description: 'Tour: Damnoen Saduak',
        successUrl: 'https://feedai.test/pay/success',
        cancelUrl: 'https://feedai.test/pay/cancel',
    );

    expect($result['payment'])->toBeInstanceOf(Payment::class)
        ->and($result['payment']->status)->toBe('pending')
        ->and($result['payment']->amount_cents)->toBe(120000)
        ->and($result['payment']->currency)->toBe('THB')
        ->and($result['payment']->method)->toBe('stripe')
        ->and($result['payment']->vendor_id)->toBe($vendor->id)
        ->and($result['payment']->provider_reference)->toBe('cs_test_fixed_1')
        ->and($result['checkout_url'])->toBe('https://checkout.stripe.com/cs_test_fixed_1')
        ->and($captured->params['mode'])->toBe('payment')
        ->and($captured->params['line_items'][0]['price_data']['currency'])->toBe('thb')
        ->and($captured->params['line_items'][0]['price_data']['unit_amount'])->toBe(120000)
        ->and($captured->params['metadata']['payment_id'])->toBe((string) $result['payment']->id);
});

it('skips transfer_data and uses the platform charge in demo mode', function () {
    config()->set('services.stripe.demo_mode', true);

    [$client, $captured] = mockStripeClient('cs_demo_1', 'https://checkout.stripe.com/cs_demo_1');
    $service = new StripeCheckout($client);
    $vendor = Vendor::factory()->withStripeConnect()->create();

    $result = $service->fixedAmount(
        $vendor,
        amountCents: 50000,
        currency: 'THB',
        description: 'Quick pay',
        successUrl: 'https://feedai.test/feed',
        cancelUrl: 'https://feedai.test/pay',
    );

    // Real Stripe call goes out, but with NO transfer_data (so the demo
    // vendor's fake account isn't required by Stripe) and a session_id
    // placeholder appended to the success_url so the return can verify.
    expect($captured->params['success_url'])->toBe('https://feedai.test/feed?session_id={CHECKOUT_SESSION_ID}')
        ->and($captured->params['payment_intent_data'])->not->toHaveKey('transfer_data')
        ->and($captured->params['metadata']['demo'])->toBe('1')
        ->and($result['payment']->status)->toBe('pending')
        ->and($result['checkout_url'])->toBe('https://checkout.stripe.com/cs_demo_1');
});

it('handles custom amounts the same way as fixed amounts', function () {
    [$client, $captured] = mockStripeClient('cs_test_custom_1', 'https://checkout.stripe.com/cs_test_custom_1');
    $service = new StripeCheckout($client);
    $vendor = Vendor::factory()->withStripeConnect()->create();

    $result = $service->customAmount(
        $vendor,
        amountCents: 8500,
        currency: 'thb',
        description: 'Tip',
        successUrl: 'https://feedai.test/ok',
        cancelUrl: 'https://feedai.test/no',
    );

    expect($result['payment']->amount_cents)->toBe(8500)
        ->and($result['payment']->description)->toBe('Tip')
        ->and($result['payment']->currency)->toBe('THB');
});

it('sums multi-item totals and forwards line items to stripe', function () {
    [$client, $captured] = mockStripeClient('cs_test_multi_1', 'https://checkout.stripe.com/cs_test_multi_1');
    $service = new StripeCheckout($client);
    $vendor = Vendor::factory()->withStripeConnect()->create();

    $result = $service->multiItem(
        $vendor,
        items: [
            ['amount_cents' => 15000, 'description' => 'Pad Thai', 'quantity' => 2],
            ['amount_cents' => 4000, 'description' => 'Thai Iced Tea'],
        ],
        currency: 'THB',
        successUrl: 'https://feedai.test/ok',
        cancelUrl: 'https://feedai.test/no',
    );

    expect($result['payment']->amount_cents)->toBe(15000 * 2 + 4000)
        ->and($result['payment']->metadata['items'])->toHaveCount(2)
        ->and($captured->params['line_items'])->toHaveCount(2)
        ->and($captured->params['line_items'][0]['quantity'])->toBe(2);
});

it('binds the stripe client from the container in production code', function () {
    expect(app(StripeClient::class))->toBeInstanceOf(StripeClient::class);
});

it('attaches Destination Charge transfer_data to the session', function () {
    [$client, $captured] = mockStripeClient('cs_test_dest_1', 'https://checkout.stripe.com/cs_test_dest_1');
    $service = new StripeCheckout($client);
    $vendor = Vendor::factory()->withStripeConnect()->create();

    $service->fixedAmount($vendor, 50000, 'THB', 'Tour', 'https://ok', 'https://no');

    expect($captured->params['payment_intent_data']['transfer_data']['destination'])
        ->toBe($vendor->stripe_account_id);
});

it('refuses to create a session if vendor has not completed Stripe Connect onboarding', function () {
    // Fresh mock that NEVER expects ->create() — we should bail before it.
    $sessions = Mockery::mock(SessionService::class);
    $sessions->shouldNotReceive('create');
    $checkout = Mockery::mock(CheckoutServiceFactory::class);
    $checkout->sessions = $sessions;
    $client = Mockery::mock(StripeClient::class);
    $client->checkout = $checkout;

    $service = new StripeCheckout($client);
    $vendor = Vendor::factory()->create(); // no stripe_account_id

    expect(fn () => $service->fixedAmount($vendor, 1000, 'THB', 'x', 'https://ok', 'https://no'))
        ->toThrow(RuntimeException::class, 'Stripe Connect Express onboarding incomplete');

    expect(Payment::count())->toBe(0);
});

it('applies a platform fee when STRIPE_PLATFORM_FEE_BPS is configured', function () {
    config(['services.stripe.platform_fee_bps' => 250]); // 2.5%

    [$client, $captured] = mockStripeClient('cs_test_fee_1', 'https://checkout.stripe.com/cs_test_fee_1');
    $service = new StripeCheckout($client);
    $vendor = Vendor::factory()->withStripeConnect()->create();

    $service->fixedAmount($vendor, 100000, 'THB', 'Tour', 'https://ok', 'https://no');

    expect($captured->params['payment_intent_data']['application_fee_amount'])->toBe(2500);
});

it('omits application_fee_amount when fee is zero', function () {
    config(['services.stripe.platform_fee_bps' => 0]);

    [$client, $captured] = mockStripeClient('cs_test_fee_0', 'https://checkout.stripe.com/cs_test_fee_0');
    $service = new StripeCheckout($client);
    $vendor = Vendor::factory()->withStripeConnect()->create();

    $service->fixedAmount($vendor, 100000, 'THB', 'Tour', 'https://ok', 'https://no');

    expect($captured->params['payment_intent_data'])
        ->not->toHaveKey('application_fee_amount');
});
