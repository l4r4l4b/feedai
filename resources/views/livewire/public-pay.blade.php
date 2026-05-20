@php
    $chainLabel = [
        'ETH' => 'Ethereum',
        'POL' => 'Polygon',
        'SOL' => 'Solana',
        'TRC20' => 'Tron TRC20',
        'BTC' => 'Bitcoin',
    ];
    $tabLabel = [
        'card' => 'Card',
        'promptpay' => 'PromptPay',
        'crypto' => 'Crypto',
    ];
    $tabIcon = [
        'card' => '💳',
        'promptpay' => '📱',
        'crypto' => '🪙',
    ];
    $methods = $this->availableMethods;
    $presetAmounts = [10000, 25000, 50000, 100000];
@endphp

<div class="flex flex-col gap-6">
    {{-- Header --}}
    <header class="space-y-3">
        <a href="{{ url('/'.$vendor->slug) }}" class="inline-flex items-center gap-1 text-caption uppercase tracking-wide text-muted transition hover:text-ink">
            ← {{ $vendor->name }}
        </a>
        <h1 class="text-hero leading-tight text-ink">Pay {{ $vendor->name }}</h1>
        <p class="text-body text-muted">
            FeedAI never holds your money — every payment goes straight to the vendor. Pick the method that works for you.
        </p>
    </header>

    @if (empty($methods))
        <div class="rounded-lg border border-line bg-surface p-8 text-center">
            <p class="text-display text-muted">💤</p>
            <p class="mt-3 text-body text-text">{{ __('This vendor hasn\'t set up any payment methods yet.') }}</p>
            <p class="mt-1 text-caption text-muted">{{ __('Get in touch directly from their feed.') }}</p>
        </div>
    @else
        {{-- Tab bar — segmented control style, hides itself if only one method --}}
        @if (count($methods) > 1)
            <nav class="grid w-full grid-cols-{{ count($methods) }} gap-1 rounded-full border border-line bg-surface p-1">
                @foreach ($methods as $method)
                    <button
                        type="button"
                        wire:click="setTab('{{ $method }}')"
                        @class([
                            'flex items-center justify-center gap-1.5 rounded-full px-3 py-2 text-label transition',
                            'bg-ink text-canvas shadow-sm' => $activeTab === $method,
                            'text-text hover:bg-canvas' => $activeTab !== $method,
                        ])
                    >
                        <span aria-hidden="true">{{ $tabIcon[$method] }}</span>
                        <span>{{ $tabLabel[$method] }}</span>
                    </button>
                @endforeach
            </nav>
        @endif

        {{-- =========================== Card =========================== --}}
        @if ($activeTab === 'card' && $vendor->acceptsCards())
            <section class="space-y-4">
                <div class="rounded-lg border border-line bg-canvas p-5">
                    <p class="text-caption uppercase tracking-wide text-muted">{{ __('Pick an amount') }}</p>
                    <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
                        @foreach ($presetAmounts as $cents)
                            <button
                                type="button"
                                wire:click="payFixed({{ $cents }}, '{{ __('Quick pay') }}')"
                                class="rounded-md border border-line bg-canvas px-4 py-3 text-section text-ink transition hover:border-ink hover:bg-surface"
                            >
                                {{ number_format($cents / 100, 0) }}
                                <span class="text-caption text-muted">THB</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-lg border border-line bg-canvas p-5">
                    <p class="text-caption uppercase tracking-wide text-muted">{{ __('Or set your own') }}</p>
                    <form wire:submit="payCustom" class="mt-3 flex flex-wrap items-stretch gap-2">
                        <div class="flex flex-1 items-center rounded-md border border-line bg-surface px-3 focus-within:border-ink">
                            <input
                                wire:model="customAmount"
                                type="number"
                                step="0.01"
                                min="0.01"
                                placeholder="350"
                                class="w-full bg-transparent py-2.5 text-section text-ink placeholder:text-soft-muted focus:outline-none"
                                inputmode="decimal"
                            />
                            <span class="text-caption text-muted">THB</span>
                        </div>
                        <flux:button type="submit" variant="primary">
                            {{ __('Pay') }}
                        </flux:button>
                    </form>
                    @error('customAmount')
                        <p class="mt-2 text-caption text-[#B5564D]">{{ $message }}</p>
                    @enderror
                </div>

                <p class="text-caption text-muted">
                    {{ __('Card payments are processed by Stripe. The vendor receives the money directly within 2–3 business days.') }}
                </p>
            </section>
        @endif

        {{-- =========================== PromptPay =========================== --}}
        @if ($activeTab === 'promptpay' && $vendor->acceptsPromptPay())
            <section class="space-y-4">
                <div class="flex flex-col items-center gap-4 rounded-lg border border-line bg-canvas p-6">
                    <p class="text-caption uppercase tracking-wide text-muted">{{ __('Scan with your banking app') }}</p>
                    <div class="rounded-md border border-line bg-surface p-3" data-test="promptpay-qr">
                        {!! $this->promptpayQrSvg !!}
                    </div>
                    <p class="text-center text-caption text-muted">
                        {{ __('Money lands instantly in the vendor\'s Thai bank account. No fees, no platform in between.') }}
                    </p>
                </div>

                <details class="rounded-md border border-line bg-surface px-4 py-3 text-caption text-muted">
                    <summary class="cursor-pointer">{{ __('Show EMV payload') }}</summary>
                    <code class="mt-2 block break-all font-mono text-[11px]" data-test="promptpay-payload">{{ $this->promptpayPayload }}</code>
                </details>
            </section>
        @endif

        {{-- =========================== Crypto =========================== --}}
        @if ($activeTab === 'crypto' && $vendor->acceptsStablecoin())
            <section
                class="space-y-4"
                x-data="{
                    copied: false,
                    copy() {
                        navigator.clipboard.writeText('{{ $vendor->stablecoin_address }}');
                        this.copied = true;
                        setTimeout(() => this.copied = false, 1500);
                    }
                }"
            >
                <div class="rounded-lg border border-line bg-canvas p-5">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-caption uppercase tracking-wide text-muted">{{ __('Wallet address') }}</p>
                        <flux:badge color="zinc" size="sm" inset="top bottom">
                            {{ $chainLabel[$vendor->stablecoin_chain] ?? $vendor->stablecoin_chain }}
                        </flux:badge>
                    </div>

                    <code
                        class="mt-3 block break-all rounded-md border border-line bg-surface px-3 py-3 font-mono text-[12px] text-ink"
                        data-test="crypto-address"
                    >
                        {{ $vendor->stablecoin_address }}
                    </code>

                    <button
                        type="button"
                        x-on:click="copy()"
                        data-test="crypto-copy"
                        class="mt-3 inline-flex items-center gap-2 rounded-full bg-ink px-5 py-2.5 text-label text-canvas transition hover:opacity-90"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                            <rect x="9" y="9" width="13" height="13" rx="2"/>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                        </svg>
                        <span x-show="!copied">{{ __('Copy address') }}</span>
                        <span x-show="copied" x-cloak>{{ __('Copied ✓') }}</span>
                    </button>
                </div>

                <p class="text-caption text-muted">
                    {{ __('The vendor controls this wallet themselves. FeedAI only displays the address — no wallet-connect, no platform fees.') }}
                </p>
            </section>
        @endif
    @endif
</div>
