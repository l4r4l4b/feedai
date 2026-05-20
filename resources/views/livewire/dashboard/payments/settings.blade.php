@php
    $chains = ['ETH' => 'Ethereum (USDC/USDT)', 'POL' => 'Polygon (USDC)', 'SOL' => 'Solana (USDC)', 'TRC20' => 'Tron TRC20 (USDT)', 'BTC' => 'Bitcoin (BTC)'];
@endphp

<div class="flex flex-col gap-6 p-6 lg:p-8 max-w-3xl">
    <header>
        <flux:heading size="xl">Payment methods</flux:heading>
        <flux:text class="mt-1 text-muted">
            FeedAI never holds your money. Cards are routed straight to you via Stripe; PromptPay and crypto we only display.
        </flux:text>
    </header>

    {{-- ============================================================ --}}
    {{-- Stripe (Card) — Platform-Processed via Connect Express      --}}
    {{-- ============================================================ --}}
    <section class="rounded-lg border border-line bg-canvas p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <flux:heading size="lg">Credit card</flux:heading>
                    @if ($vendor->acceptsCards())
                        <flux:badge color="lime" size="sm" inset="top bottom">Active</flux:badge>
                    @elseif ($vendor->stripe_account_id)
                        <flux:badge color="amber" size="sm" inset="top bottom">Onboarding pending</flux:badge>
                    @else
                        <flux:badge color="zinc" size="sm" inset="top bottom">Not set up</flux:badge>
                    @endif
                </div>
                <flux:text class="mt-2 text-muted text-caption">
                    Tourist pays by card → Stripe routes the money straight to your payout account. ~2 minutes to set up.
                </flux:text>
            </div>
        </div>

        <div class="mt-4">
            @if ($vendor->acceptsCards())
                <div class="flex items-center gap-3">
                    <flux:button wire:click="startStripeOnboarding" variant="ghost" size="sm">
                        Open payout account
                    </flux:button>
                    <span class="text-caption uppercase tracking-wide text-muted">
                        {{ \Illuminate\Support\Str::limit($vendor->stripe_account_id, 22) }}
                    </span>
                </div>
            @else
                <flux:button wire:click="startStripeOnboarding" variant="primary">
                    {{ $vendor->stripe_account_id ? 'Continue onboarding' : 'Set up payout account' }}
                </flux:button>
            @endif
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- Direct Payments — PromptPay + Crypto                          --}}
    {{-- ============================================================ --}}
    <form wire:submit="saveDirectPayments" class="flex flex-col gap-6">
        <section class="rounded-lg border border-line bg-canvas p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <flux:heading size="lg">PromptPay</flux:heading>
                        @if ($vendor->acceptsPromptPay())
                            <flux:badge color="lime" size="sm" inset="top bottom">Active</flux:badge>
                        @endif
                    </div>
                    <flux:text class="mt-2 text-muted text-caption">
                        We generate a QR code from your PromptPay number. The tourist scans it with their banking app and the money goes straight to you.
                    </flux:text>
                </div>
            </div>

            <div class="mt-4">
                <flux:input
                    wire:model="promptpayPhone"
                    label="PromptPay number"
                    placeholder="0812345678 or +66812345678"
                    description="Thai mobile number or 13-digit national ID"
                />
            </div>
        </section>

        <section class="rounded-lg border border-line bg-canvas p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <flux:heading size="lg">Crypto</flux:heading>
                        @if ($vendor->acceptsStablecoin())
                            <flux:badge color="lime" size="sm" inset="top bottom">Active</flux:badge>
                        @endif
                    </div>
                    <flux:text class="mt-2 text-muted text-caption">
                        Wallet address plus chain. We display the address with a copy button — no wallet-connect required.
                    </flux:text>
                </div>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-3">
                <div class="md:col-span-2">
                    <flux:input
                        wire:model="stablecoinAddress"
                        label="Wallet address"
                        placeholder="0x... or bc1..."
                    />
                </div>
                <flux:select wire:model="stablecoinChain" label="Chain" placeholder="Select">
                    @foreach ($chains as $code => $label)
                        <flux:select.option value="{{ $code }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </section>

        <div class="flex justify-end">
            <flux:button type="submit" variant="primary">Save</flux:button>
        </div>
    </form>
</div>
