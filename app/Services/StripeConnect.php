<?php

namespace App\Services;

use App\Models\Vendor;
use Illuminate\Support\Str;
use Stripe\Account;
use Stripe\AccountLink;
use Stripe\StripeClient;

/**
 * Stripe Connect Express lifecycle.
 *
 * FeedAI is an aggregator, not a processor: charges go via Destination Charge
 * to the vendor's own Express account. This service handles the three steps
 * around that:
 *
 *  1. createExpressAccount() — provision the vendor's connected account
 *  2. createOnboardingLink()  — Stripe-hosted onboarding URL (2 min flow)
 *  3. syncAccountStatus()      — pull charges_enabled / details_submitted
 *
 * The webhook `account.updated` does the same sync push-driven; this service
 * is the pull/init counterpart.
 */
class StripeConnect
{
    public function __construct(
        protected StripeClient $stripe,
    ) {}

    /**
     * Demo mode skips every Stripe API call. Enabled automatically when no
     * STRIPE_SECRET is configured — flips vendor flags locally so the rest
     * of the app (acceptsCards(), payments page) behaves as if onboarded.
     */
    public function isDemoMode(): bool
    {
        return (bool) config('services.stripe.demo_mode');
    }

    /**
     * Mark a vendor as Stripe-active without ever hitting Stripe. Used as
     * the demo path when there's no live Stripe Connect platform configured.
     */
    public function activateDemo(Vendor $vendor): void
    {
        if (! $vendor->stripe_account_id) {
            $vendor->stripe_account_id = 'acct_demo_'.Str::lower(Str::random(16));
        }

        $vendor->forceFill([
            'stripe_account_id' => $vendor->stripe_account_id,
            'stripe_charges_enabled' => true,
            'stripe_details_submitted' => true,
        ])->save();
    }

    /**
     * Provision a Stripe Express account for the vendor (idempotent).
     * Returns the existing account if the vendor already has one.
     */
    public function createExpressAccount(Vendor $vendor, string $country = 'TH'): Account
    {
        if ($vendor->stripe_account_id) {
            return $this->stripe->accounts->retrieve($vendor->stripe_account_id);
        }

        $account = $this->stripe->accounts->create([
            'type' => 'express',
            'country' => $country,
            'email' => $vendor->user?->email,
            'capabilities' => [
                'card_payments' => ['requested' => true],
                'transfers' => ['requested' => true],
            ],
            'business_type' => 'individual',
            'business_profile' => [
                'name' => $vendor->name,
                'url' => url('/'.$vendor->slug),
            ],
            'metadata' => [
                'vendor_id' => (string) $vendor->id,
                'vendor_slug' => $vendor->slug,
            ],
        ]);

        $vendor->update([
            'stripe_account_id' => $account->id,
            'stripe_charges_enabled' => $account->charges_enabled,
            'stripe_details_submitted' => $account->details_submitted,
        ]);

        return $account;
    }

    /**
     * Stripe-hosted onboarding URL — Vendor klickt drauf, schließt in ~2min ab.
     * Falls Vendor abbricht: `refresh_url` regeneriert den Link.
     */
    public function createOnboardingLink(Vendor $vendor, string $returnUrl, string $refreshUrl): AccountLink
    {
        if (! $vendor->stripe_account_id) {
            $this->createExpressAccount($vendor);
            $vendor->refresh();
        }

        return $this->stripe->accountLinks->create([
            'account' => $vendor->stripe_account_id,
            'refresh_url' => $refreshUrl,
            'return_url' => $returnUrl,
            'type' => 'account_onboarding',
        ]);
    }

    /**
     * Pull current account state from Stripe and sync to DB. Useful right after
     * a Vendor returns from the onboarding redirect (before any webhook fires).
     */
    public function syncAccountStatus(Vendor $vendor): void
    {
        if (! $vendor->stripe_account_id) {
            return;
        }

        $account = $this->stripe->accounts->retrieve($vendor->stripe_account_id);

        $vendor->update([
            'stripe_charges_enabled' => $account->charges_enabled,
            'stripe_details_submitted' => $account->details_submitted,
        ]);
    }
}
