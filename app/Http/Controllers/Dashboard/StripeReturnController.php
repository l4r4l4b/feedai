<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\StripeConnect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Endpoints Stripe redirects the vendor back to after the Connect Express
 * onboarding flow. We don't rely on the webhook alone — pulling once on
 * return guarantees the UI reflects the latest state immediately.
 */
class StripeReturnController extends Controller
{
    /**
     * Vendor completed (or partially completed) Stripe-hosted onboarding.
     */
    public function return(Request $request, StripeConnect $stripe): RedirectResponse
    {
        $vendor = $request->user()?->vendor;

        if ($vendor) {
            $stripe->syncAccountStatus($vendor);
        }

        return redirect()
            ->route('dashboard.payments.settings')
            ->with('status', __('Auszahlungskonto-Status aktualisiert.'));
    }

    /**
     * Vendor's onboarding link expired (Stripe links are short-lived).
     * Generate a fresh one and redirect back to Stripe.
     */
    public function refresh(Request $request, StripeConnect $stripe): RedirectResponse
    {
        $vendor = $request->user()?->vendor;

        if (! $vendor) {
            return redirect()->route('dashboard.payments.settings');
        }

        $link = $stripe->createOnboardingLink(
            $vendor,
            returnUrl: route('dashboard.payments.stripe.return'),
            refreshUrl: route('dashboard.payments.stripe.refresh'),
        );

        return redirect()->away($link->url);
    }
}
