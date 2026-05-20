<?php

namespace App\Livewire\Dashboard\Payments;

use App\Models\Vendor;
use App\Services\StripeConnect;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Title('Payment Settings')]
class Settings extends Component
{
    public Vendor $vendor;

    #[Validate('nullable|string|max:32')]
    public string $promptpayPhone = '';

    #[Validate('nullable|string|max:128')]
    public string $stablecoinAddress = '';

    #[Validate('nullable|in:ETH,SOL,TRC20,BTC,POL')]
    public string $stablecoinChain = '';

    public function mount(): void
    {
        $vendor = Auth::user()?->vendor;

        if (! $vendor) {
            abort(404, 'Vendor profile not yet created.');
        }

        $this->vendor = $vendor;
        $this->promptpayPhone = (string) ($vendor->promptpay_phone ?? '');
        $this->stablecoinAddress = (string) ($vendor->stablecoin_address ?? '');
        $this->stablecoinChain = (string) ($vendor->stablecoin_chain ?? '');
    }

    public function saveDirectPayments(): void
    {
        $this->validate();

        $this->vendor->update([
            'promptpay_phone' => $this->promptpayPhone ?: null,
            'stablecoin_address' => $this->stablecoinAddress ?: null,
            'stablecoin_chain' => $this->stablecoinChain ?: null,
        ]);

        Flux::toast(variant: 'success', text: __('Direct payment methods saved.'));
    }

    public function startStripeOnboarding(StripeConnect $stripe)
    {
        $vendor = $this->vendor->fresh();

        // Demo path: no real Stripe credentials → instantly mark the vendor
        // as active. Lets the hackathon demo end with a working "Active"
        // state instead of a Stripe API error.
        if ($stripe->isDemoMode()) {
            $stripe->activateDemo($vendor);
            $this->vendor = $vendor->fresh();
            Flux::toast(variant: 'success', text: __('Stripe activated in demo mode — cards are accepted on your feed.'));

            return null;
        }

        $link = $stripe->createOnboardingLink(
            $vendor,
            returnUrl: route('dashboard.payments.stripe.return'),
            refreshUrl: route('dashboard.payments.stripe.refresh'),
        );

        return redirect()->away($link->url);
    }

    public function render(): View
    {
        return view('livewire.dashboard.payments.settings');
    }
}
