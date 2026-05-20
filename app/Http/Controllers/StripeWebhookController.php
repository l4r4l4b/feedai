<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $secret = config('services.stripe.webhook_secret');

        if (! $secret) {
            Log::error('Stripe webhook secret not configured');

            return response('Webhook secret not configured', 500);
        }

        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature', ''),
                $secret,
            );
        } catch (SignatureVerificationException|UnexpectedValueException $e) {
            return response('Invalid signature: '.$e->getMessage(), 400);
        }

        match ($event->type) {
            'checkout.session.completed' => $this->markPaid($event),
            'payment_intent.payment_failed' => $this->markFailed($event),
            'account.updated' => $this->syncConnectAccount($event),
            default => Log::info('Stripe webhook ignored', ['type' => $event->type]),
        };

        return response('', 200);
    }

    /**
     * Sync vendor Connect status when Stripe pushes account.updated.
     * Fires when the vendor finishes Express onboarding and again whenever
     * Stripe re-verifies their identity / docs.
     */
    protected function syncConnectAccount(Event $event): void
    {
        $account = $event->data->object;

        $vendor = Vendor::where('stripe_account_id', $account->id)->first();

        if (! $vendor) {
            Log::info('account.updated for unknown vendor', ['account' => $account->id]);

            return;
        }

        $vendor->update([
            'stripe_charges_enabled' => (bool) ($account->charges_enabled ?? false),
            'stripe_details_submitted' => (bool) ($account->details_submitted ?? false),
        ]);
    }

    protected function markPaid(Event $event): void
    {
        $session = $event->data->object;
        $paymentId = $session->metadata->payment_id ?? null;

        if (! $paymentId) {
            Log::warning('checkout.session.completed without payment_id metadata', ['session' => $session->id]);

            return;
        }

        Payment::whereKey($paymentId)->update([
            'status' => 'paid',
            'paid_at' => now(),
            'provider_reference' => $session->payment_intent ?? $session->id,
        ]);
    }

    protected function markFailed(Event $event): void
    {
        $intent = $event->data->object;
        $paymentId = $intent->metadata->payment_id ?? null;

        if (! $paymentId) {
            return;
        }

        Payment::whereKey($paymentId)->update(['status' => 'failed']);
    }
}
