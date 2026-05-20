<?php

namespace App\Livewire;

use App\Models\Vendor;
use App\Services\StripeCheckout;
use App\Support\Locale;
use App\Support\PromptPayPayload;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Pay')]
class PublicPay extends Component
{
    public Vendor $vendor;

    public string $activeTab = '';

    public string $customAmount = '';

    /**
     * Livewire's resolveComponentProps already populates $this->vendor via
     * route-model-binding (Vendor::getRouteKeyName() === 'slug'). The $vendor
     * parameter here is the same model — we only use it to guard status.
     */
    public function mount(Vendor $vendor): void
    {
        abort_unless($vendor->status === 'live', 404);

        app()->setLocale(Locale::resolve(request(), $vendor->locale));

        $this->activeTab = match (true) {
            $this->vendor->acceptsCards() => 'card',
            $this->vendor->acceptsPromptPay() => 'promptpay',
            $this->vendor->acceptsStablecoin() => 'crypto',
            default => '',
        };
    }

    #[Computed]
    public function availableMethods(): array
    {
        return array_values(array_filter([
            $this->vendor->acceptsCards() ? 'card' : null,
            $this->vendor->acceptsPromptPay() ? 'promptpay' : null,
            $this->vendor->acceptsStablecoin() ? 'crypto' : null,
        ]));
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['card', 'promptpay', 'crypto'], true)) {
            $this->activeTab = $tab;
        }
    }

    public function payFixed(StripeCheckout $checkout, int $amountCents, string $description)
    {
        return redirect()->away($checkout->fixedAmount(
            $this->vendor,
            amountCents: $amountCents,
            currency: 'THB',
            description: $description,
            successUrl: route('feed.show', ['vendor' => $this->vendor->slug]),
            cancelUrl: route('public.pay', ['vendor' => $this->vendor->slug]),
        )['checkout_url']);
    }

    public function payCustom(StripeCheckout $checkout)
    {
        $amount = (float) str_replace(',', '.', $this->customAmount);

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'customAmount' => __('Please enter an amount greater than 0.'),
            ]);
        }

        return $this->payFixed($checkout, (int) round($amount * 100), __('Tip for :name', ['name' => $this->vendor->name]));
    }

    #[Computed]
    public function promptpayPayload(): ?string
    {
        if (! $this->vendor->acceptsPromptPay()) {
            return null;
        }

        return (string) PromptPayPayload::fromPhone($this->vendor->promptpay_phone);
    }

    #[Computed]
    public function promptpayQrSvg(): ?string
    {
        if (! $this->promptpayPayload) {
            return null;
        }

        return (new Builder(
            writer: new SvgWriter,
            data: $this->promptpayPayload,
            size: 280,
            margin: 8,
        ))->build()->getString();
    }

    /**
     * QR for the vendor's crypto wallet. Encodes a chain-specific payment URI
     * when available (ethereum:/bitcoin:/solana:) so a wallet app can pre-fill
     * the address; falls back to the raw address for chains without a
     * standardised URI scheme.
     */
    #[Computed]
    public function cryptoQrSvg(): ?string
    {
        if (! $this->vendor->acceptsStablecoin()) {
            return null;
        }

        $address = (string) $this->vendor->stablecoin_address;
        $chain = (string) $this->vendor->stablecoin_chain;

        $payload = match ($chain) {
            'ETH', 'POL' => 'ethereum:'.$address,
            'BTC' => 'bitcoin:'.$address,
            'SOL' => 'solana:'.$address,
            default => $address,
        };

        return (new Builder(
            writer: new SvgWriter,
            data: $payload,
            size: 220,
            margin: 6,
        ))->build()->getString();
    }

    public function render(): View
    {
        $vendorArray = [
            'slug' => $this->vendor->slug,
            'name' => $this->vendor->name,
            'locale' => $this->vendor->locale,
            'accent_color' => $this->vendor->accent_color,
        ];

        return view('livewire.public-pay')->layout('layouts.public', [
            'vendor' => $vendorArray,
            'page' => 'pay',
        ]);
    }
}
