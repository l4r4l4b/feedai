<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Vendor;
use RuntimeException;
use Stripe\Checkout\Session;
use Stripe\StripeClient;

/**
 * Thin wrapper around Stripe Checkout Sessions, configured as Destination
 * Charges: the money flows directly to the vendor's Stripe Connect Express
 * account. FeedAI never holds the funds — we are an aggregator, not a
 * processor. Optional platform fees can be set via STRIPE_PLATFORM_FEE_BPS.
 *
 * Pre-creates a Payment row in status=pending so we can correlate the
 * webhook (which carries payment_id in session metadata) back to a record.
 *
 * @phpstan-type MultiLineItem array{amount_cents: int, description: string, quantity?: int}
 */
class StripeCheckout
{
    public function __construct(
        protected StripeClient $stripe,
    ) {}

    /**
     * One fixed-price item — e.g. "Tour: Damnoen Saduak — 1200 THB".
     */
    public function fixedAmount(
        Vendor $vendor,
        int $amountCents,
        string $currency,
        string $description,
        string $successUrl,
        string $cancelUrl,
    ): array {
        $this->ensureVendorAcceptsCards($vendor);

        $payment = $this->createPendingPayment($vendor, $amountCents, $currency, $description);

        $session = $this->stripe->checkout->sessions->create($this->buildSessionParams(
            vendor: $vendor,
            payment: $payment,
            currency: $currency,
            totalCents: $amountCents,
            lineItems: [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => strtolower($currency),
                    'unit_amount' => $amountCents,
                    'product_data' => ['name' => $description],
                ],
            ]],
            successUrl: $successUrl,
            cancelUrl: $cancelUrl,
        ));

        return $this->finalize($payment, $session);
    }

    /**
     * Tourist enters their own amount — Tip jar or "pay what you can".
     * Caller is responsible for min/max validation before calling.
     */
    public function customAmount(
        Vendor $vendor,
        int $amountCents,
        string $currency,
        string $description,
        string $successUrl,
        string $cancelUrl,
    ): array {
        return $this->fixedAmount($vendor, $amountCents, $currency, $description, $successUrl, $cancelUrl);
    }

    /**
     * Multiple items in one checkout — e.g. menu order.
     *
     * @param  list<MultiLineItem>  $items
     */
    public function multiItem(
        Vendor $vendor,
        array $items,
        string $currency,
        string $successUrl,
        string $cancelUrl,
    ): array {
        $this->ensureVendorAcceptsCards($vendor);

        $total = array_sum(array_map(fn ($item) => $item['amount_cents'] * ($item['quantity'] ?? 1), $items));
        $description = count($items).' Items';

        $payment = $this->createPendingPayment($vendor, $total, $currency, $description, [
            'items' => $items,
        ]);

        $lineItems = array_map(fn ($item) => [
            'quantity' => $item['quantity'] ?? 1,
            'price_data' => [
                'currency' => strtolower($currency),
                'unit_amount' => $item['amount_cents'],
                'product_data' => ['name' => $item['description']],
            ],
        ], $items);

        $session = $this->stripe->checkout->sessions->create($this->buildSessionParams(
            vendor: $vendor,
            payment: $payment,
            currency: $currency,
            totalCents: $total,
            lineItems: $lineItems,
            successUrl: $successUrl,
            cancelUrl: $cancelUrl,
        ));

        return $this->finalize($payment, $session);
    }

    /**
     * Hard-fail if vendor hasn't completed Stripe Connect onboarding — we
     * never want to silently route to FeedAI's account.
     */
    protected function ensureVendorAcceptsCards(Vendor $vendor): void
    {
        if (! $vendor->acceptsCards()) {
            throw new RuntimeException(
                "Vendor [{$vendor->slug}] cannot accept card payments — Stripe Connect Express onboarding incomplete.",
            );
        }
    }

    protected function buildSessionParams(
        Vendor $vendor,
        Payment $payment,
        string $currency,
        int $totalCents,
        array $lineItems,
        string $successUrl,
        string $cancelUrl,
    ): array {
        $metadata = $this->sessionMetadata($payment);

        return [
            'mode' => 'payment',
            'line_items' => $lineItems,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => $metadata,
            'payment_intent_data' => array_filter([
                'metadata' => $metadata,
                'application_fee_amount' => $this->platformFeeFor($totalCents),
                'transfer_data' => [
                    'destination' => $vendor->stripe_account_id,
                ],
            ], fn ($v) => $v !== null && $v !== []),
        ];
    }

    /**
     * Platform fee in cents, derived from STRIPE_PLATFORM_FEE_BPS (basis points).
     * Default = 0 (FeedAI takes nothing in the hackathon).
     */
    protected function platformFeeFor(int $totalCents): ?int
    {
        $bps = (int) config('services.stripe.platform_fee_bps', 0);

        if ($bps <= 0) {
            return null;
        }

        return (int) floor($totalCents * $bps / 10000);
    }

    protected function createPendingPayment(
        Vendor $vendor,
        int $amountCents,
        string $currency,
        string $description,
        array $extraMetadata = [],
    ): Payment {
        return Payment::create([
            'vendor_id' => $vendor->id,
            'amount_cents' => $amountCents,
            'currency' => strtoupper($currency),
            'method' => 'stripe',
            'status' => 'pending',
            'description' => $description,
            'metadata' => $extraMetadata,
        ]);
    }

    /**
     * @return array{payment_id: string, vendor_id: string}
     */
    protected function sessionMetadata(Payment $payment): array
    {
        return [
            'payment_id' => (string) $payment->id,
            'vendor_id' => (string) $payment->vendor_id,
        ];
    }

    /**
     * @return array{payment: Payment, session: Session, checkout_url: string|null}
     */
    protected function finalize(Payment $payment, Session $session): array
    {
        $payment->update([
            'provider_reference' => $session->id,
            'metadata' => array_merge($payment->metadata ?? [], [
                'session_id' => $session->id,
            ]),
        ]);

        return [
            'payment' => $payment->fresh(),
            'session' => $session,
            'checkout_url' => $session->url,
        ];
    }
}
