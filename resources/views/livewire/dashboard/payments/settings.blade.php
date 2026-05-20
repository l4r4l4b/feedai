@php
    $chains = ['ETH' => 'Ethereum (USDC/USDT)', 'POL' => 'Polygon (USDC)', 'SOL' => 'Solana (USDC)', 'TRC20' => 'Tron TRC20 (USDT)', 'BTC' => 'Bitcoin (BTC)'];
    $activeCount = ($vendor->acceptsCards() ? 1 : 0) + ($vendor->acceptsPromptPay() ? 1 : 0) + ($vendor->acceptsStablecoin() ? 1 : 0);
@endphp

<div class="mx-auto flex max-w-3xl flex-col gap-6 p-6 lg:p-8">
    <header class="space-y-3">
        <flux:heading size="xl">Payment methods</flux:heading>
        <flux:text class="text-muted">
            FeedAI never holds your money. Guests pay you directly through three independent rails, you pick which ones make sense for your business. Activate at least one to accept bookings.
        </flux:text>

        <div class="flex items-center gap-3 rounded-lg border border-line bg-surface px-4 py-3">
            <flux:badge :color="$activeCount > 0 ? 'lime' : 'amber'" size="sm">
                {{ $activeCount }} / 3 active
            </flux:badge>
            <flux:text size="sm" class="text-muted">
                @if ($activeCount === 0)
                    No payment methods enabled yet, pick at least one below.
                @elseif ($activeCount === 1)
                    Good start. Add another method to give guests more options.
                @else
                    You're set, guests will see all active methods at /{{ $vendor->slug }}/pay.
                @endif
            </flux:text>
        </div>
    </header>

    {{-- ============================================================ --}}
    {{-- Stripe (Card), Platform-Processed via Connect Express        --}}
    {{-- ============================================================ --}}
    <section class="rounded-lg border border-line bg-canvas p-6">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
                <div class="flex flex-wrap items-center gap-3">
                    <flux:heading size="lg">Credit card</flux:heading>
                    @if ($vendor->acceptsCards())
                        <flux:badge color="lime" size="sm" inset="top bottom">Active</flux:badge>
                    @elseif ($vendor->stripe_account_id)
                        <flux:badge color="amber" size="sm" inset="top bottom">Onboarding pending</flux:badge>
                    @else
                        <flux:badge color="zinc" size="sm" inset="top bottom">Not set up</flux:badge>
                    @endif
                    <flux:text size="xs" class="ml-auto text-muted">~2 min setup</flux:text>
                </div>

                <flux:text class="mt-2 text-muted">
                    Best for international visitors. Stripe handles the card form, fraud checks and payout to your bank account, FeedAI never touches the money. You'll briefly leave to verify your identity at Stripe, then come back here.
                </flux:text>

                <ul class="mt-3 space-y-1 text-caption text-muted">
                    <li>• Funded in your local currency, usually within 2–3 business days</li>
                    <li>• Apple Pay, Google Pay and 3-D Secure included automatically</li>
                    <li>• Stripe's standard fees apply (~2.9% + fixed cents)</li>
                </ul>
            </div>
        </div>

        <div class="mt-4">
            @if ($vendor->acceptsCards())
                <div class="flex flex-wrap items-center gap-3">
                    <flux:button wire:click="startStripeOnboarding" variant="ghost" size="sm">
                        Open payout dashboard
                    </flux:button>
                    <span class="font-mono text-caption text-muted">
                        {{ \Illuminate\Support\Str::limit($vendor->stripe_account_id, 22) }}
                    </span>
                </div>
            @else
                <flux:button wire:click="startStripeOnboarding" variant="primary">
                    {{ $vendor->stripe_account_id ? 'Continue Stripe onboarding' : 'Connect Stripe' }}
                </flux:button>
            @endif
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- Direct Payments, PromptPay + Crypto                          --}}
    {{-- ============================================================ --}}
    <form wire:submit="saveDirectPayments" class="flex flex-col gap-6">
        <section class="rounded-lg border border-line bg-canvas p-6">
            <div class="flex flex-wrap items-center gap-3">
                <flux:heading size="lg">PromptPay</flux:heading>
                @if ($vendor->acceptsPromptPay())
                    <flux:badge color="lime" size="sm" inset="top bottom">Active</flux:badge>
                @else
                    <flux:badge color="zinc" size="sm" inset="top bottom">Not set up</flux:badge>
                @endif
                <flux:text size="xs" class="ml-auto text-muted">~30 sec setup</flux:text>
            </div>

            <flux:text class="mt-2 text-muted">
                Best for Thai guests. We render a PromptPay QR from your phone number, the guest scans it in their banking app and the money lands directly in your account. No fees, no platform in between.
            </flux:text>

            <ul class="mt-3 space-y-1 text-caption text-muted">
                <li>• Settled instantly to your bank account</li>
                <li>• Works with every Thai banking app</li>
                <li>• Leave blank to hide this option from the pay page</li>
            </ul>

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
            <div class="flex flex-wrap items-center gap-3">
                <flux:heading size="lg">Crypto / stablecoin</flux:heading>
                @if ($vendor->acceptsStablecoin())
                    <flux:badge color="lime" size="sm" inset="top bottom">Active</flux:badge>
                @else
                    <flux:badge color="zinc" size="sm" inset="top bottom">Not set up</flux:badge>
                @endif
                <flux:text size="xs" class="ml-auto text-muted">~1 min setup</flux:text>
            </div>

            <flux:text class="mt-2 text-muted">
                Optional. For digital-savvy travellers who'd rather send USDC, USDT or BTC than swipe a card. We just show your wallet address with a copy button and a network badge, no wallet-connect, no platform fees.
            </flux:text>

            <ul class="mt-3 space-y-1 text-caption text-muted">
                <li>• Pick the chain you actually have a wallet on</li>
                <li>• Address is displayed exactly as you enter it, double-check before saving</li>
                <li>• Leave blank to hide this option</li>
            </ul>

            <div class="mt-4 grid gap-4 md:grid-cols-3">
                <div class="md:col-span-2">
                    <flux:input
                        wire:model="stablecoinAddress"
                        label="Wallet address"
                        placeholder="0x… or bc1…"
                    />
                </div>
                <flux:select wire:model="stablecoinChain" label="Chain" placeholder="Select">
                    @foreach ($chains as $code => $label)
                        <flux:select.option value="{{ $code }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </section>

        <div class="flex flex-wrap items-center justify-end gap-3">
            <flux:button type="submit" variant="ghost">Save changes</flux:button>
        </div>
    </form>

    {{-- Completion CTA, returns to the dashboard --}}
    <div class="mt-2 flex flex-col items-end gap-3 border-t border-line pt-6">
        <flux:text size="sm" class="text-muted">
            @if ($activeCount > 0)
                Your active methods will show up at <span class="font-mono">/{{ $vendor->slug }}/pay</span>. You can come back any time to add more.
            @else
                You can skip for now and set this up later, your feed stays live either way.
            @endif
        </flux:text>

        <flux:button
            :href="route('dashboard')"
            wire:navigate
            variant="primary"
            icon-trailing="arrow-right"
        >
            {{ $activeCount > 0 ? 'Complete setup' : 'Skip for now' }}
        </flux:button>
    </div>
</div>
