@php
    $chainLabel = [
        'ETH' => 'Ethereum',
        'POL' => 'Polygon',
        'SOL' => 'Solana',
        'TRC20' => 'Tron TRC20',
        'BTC' => 'Bitcoin',
    ];
    $methods = $this->availableMethods;
@endphp

<div class="flex flex-col gap-6">
    <header class="flex flex-col gap-2">
        <a href="{{ url('/'.$vendor->slug) }}" class="text-caption uppercase tracking-wide text-text transition hover:text-ink">
            ← {{ $vendor->name }}
        </a>
        <h1 class="text-hero font-semibold leading-tight text-ink">Pay {{ $vendor->name }}</h1>
        <p class="text-caption text-muted">FeedAI never holds your money — everything goes straight to the vendor.</p>
    </header>

    @if (empty($methods))
        <div class="rounded-lg border border-line bg-canvas p-6 text-center text-muted">
            This vendor hasn't set up any payment methods yet.
        </div>
    @else
        {{-- Tabs --}}
        <nav class="flex gap-2 border-b border-line">
            @if ($vendor->acceptsCards())
                <button type="button" wire:click="setTab('card')"
                        @class([
                            'pb-3 pt-2 text-section transition border-b-2 -mb-px',
                            'border-ink text-ink' => $activeTab === 'card',
                            'border-transparent text-muted hover:text-ink' => $activeTab !== 'card',
                        ])>
                    Karte
                </button>
            @endif
            @if ($vendor->acceptsPromptPay())
                <button type="button" wire:click="setTab('promptpay')"
                        @class([
                            'pb-3 pt-2 text-section transition border-b-2 -mb-px',
                            'border-ink text-ink' => $activeTab === 'promptpay',
                            'border-transparent text-muted hover:text-ink' => $activeTab !== 'promptpay',
                        ])>
                    PromptPay
                </button>
            @endif
            @if ($vendor->acceptsStablecoin())
                <button type="button" wire:click="setTab('crypto')"
                        @class([
                            'pb-3 pt-2 text-section transition border-b-2 -mb-px',
                            'border-ink text-ink' => $activeTab === 'crypto',
                            'border-transparent text-muted hover:text-ink' => $activeTab !== 'crypto',
                        ])>
                    Crypto
                </button>
            @endif
        </nav>

        {{-- =========================== Card Tab =========================== --}}
        @if ($activeTab === 'card' && $vendor->acceptsCards())
            <section class="flex flex-col gap-4">
                <div class="rounded-lg border border-line bg-canvas p-5">
                    <p class="text-caption uppercase tracking-wide text-muted">Fester Betrag</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ([10000, 25000, 50000, 100000] as $cents)
                            <button type="button"
                                    wire:click="payFixed({{ $cents }}, '{{ __('Quick pay') }}')"
                                    class="rounded-full border border-line bg-canvas px-4 py-2 text-section text-ink transition hover:border-ink/40">
                                {{ number_format($cents / 100, 0) }} THB
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-lg border border-line bg-canvas p-5">
                    <p class="text-caption uppercase tracking-wide text-muted">Freier Betrag</p>
                    <form wire:submit="payCustom" class="mt-3 flex gap-2">
                        <flux:input wire:model="customAmount" type="number" step="0.01" min="0.01"
                                    placeholder="z. B. 350" class="flex-1" />
                        <flux:button type="submit" variant="primary">THB zahlen</flux:button>
                    </form>
                    @error('customAmount')
                        <p class="mt-2 text-caption text-error">{{ $message }}</p>
                    @enderror
                </div>
            </section>
        @endif

        {{-- =========================== PromptPay =========================== --}}
        @if ($activeTab === 'promptpay' && $vendor->acceptsPromptPay())
            <section class="flex flex-col items-center gap-4 rounded-lg border border-line bg-canvas p-6">
                <p class="text-caption uppercase tracking-wide text-muted">PromptPay QR</p>
                <div class="rounded-image bg-canvas p-3" data-test="promptpay-qr">
                    {!! $this->promptpayQrSvg !!}
                </div>
                <p class="text-caption text-muted">
                    Scan with your Thai banking app. The money goes straight to the vendor — FeedAI never sees the transaction.
                </p>
                <details class="w-full text-caption text-muted">
                    <summary class="cursor-pointer">Show EMV payload</summary>
                    <code class="mt-2 block break-all font-mono text-[11px]" data-test="promptpay-payload">{{ $this->promptpayPayload }}</code>
                </details>
            </section>
        @endif

        {{-- =========================== Crypto =========================== --}}
        @if ($activeTab === 'crypto' && $vendor->acceptsStablecoin())
            <section class="flex flex-col gap-4 rounded-lg border border-line bg-canvas p-6"
                    x-data="{
                        copied: false,
                        copy() {
                            navigator.clipboard.writeText('{{ $vendor->stablecoin_address }}');
                            this.copied = true;
                            setTimeout(() => this.copied = false, 1500);
                        }
                    }">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-caption uppercase tracking-wide text-muted">Wallet-Adresse</p>
                    <flux:badge color="zinc" size="sm" inset="top bottom">
                        {{ $chainLabel[$vendor->stablecoin_chain] ?? $vendor->stablecoin_chain }}
                    </flux:badge>
                </div>

                <code class="break-all rounded-sm border border-line bg-canvas px-3 py-3 font-mono text-[12px] text-ink"
                      data-test="crypto-address">
                    {{ $vendor->stablecoin_address }}
                </code>

                <button type="button" x-on:click="copy()"
                        data-test="crypto-copy"
                        class="self-start rounded-full bg-accent px-4 py-2 text-caption font-medium text-canvas transition hover:opacity-90">
                    <span x-show="!copied">Adresse kopieren</span>
                    <span x-show="copied" x-cloak>Kopiert ✓</span>
                </button>

                <p class="text-caption text-muted">
                    Vendor verwahrt diese Wallet selbst. FeedAI zeigt die Adresse nur an.
                </p>
            </section>
        @endif
    @endif
</div>
