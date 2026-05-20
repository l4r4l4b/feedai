@php
    $chains = ['ETH' => 'Ethereum (USDC/USDT)', 'POL' => 'Polygon (USDC)', 'SOL' => 'Solana (USDC)', 'TRC20' => 'Tron TRC20 (USDT)', 'BTC' => 'Bitcoin (BTC)'];
@endphp

<div class="flex flex-col gap-6 p-6 lg:p-8 max-w-3xl">
    <header>
        <flux:heading size="xl">Zahlungsmethoden</flux:heading>
        <flux:text class="mt-1 text-muted">
            FeedAI hält Dein Geld nie. Karte läuft via Stripe direkt zu Dir, PromptPay und Crypto zeigen wir nur an.
        </flux:text>
    </header>

    {{-- ============================================================ --}}
    {{-- Stripe (Karte) — Platform-Processed via Connect Express      --}}
    {{-- ============================================================ --}}
    <section class="rounded-card border border-line bg-card p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <flux:heading size="lg">Kreditkarte</flux:heading>
                    @if ($vendor->acceptsCards())
                        <flux:badge color="lime" size="sm" inset="top bottom">Aktiv</flux:badge>
                    @elseif ($vendor->stripe_account_id)
                        <flux:badge color="amber" size="sm" inset="top bottom">Onboarding offen</flux:badge>
                    @else
                        <flux:badge color="zinc" size="sm" inset="top bottom">Nicht eingerichtet</flux:badge>
                    @endif
                </div>
                <flux:text class="mt-2 text-muted text-caption">
                    Tourist zahlt mit Karte → Geld geht via Stripe direkt an Dein Auszahlungskonto. ~2 Minuten Einrichtung.
                </flux:text>
            </div>
        </div>

        <div class="mt-4">
            @if ($vendor->acceptsCards())
                <div class="flex items-center gap-3">
                    <flux:button wire:click="startStripeOnboarding" variant="ghost" size="sm">
                        Auszahlungskonto öffnen
                    </flux:button>
                    <span class="font-mono text-mono-label uppercase text-muted">
                        {{ \Illuminate\Support\Str::limit($vendor->stripe_account_id, 22) }}
                    </span>
                </div>
            @else
                <flux:button wire:click="startStripeOnboarding" variant="primary">
                    {{ $vendor->stripe_account_id ? 'Onboarding fortsetzen' : 'Auszahlungskonto einrichten' }}
                </flux:button>
            @endif
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- Direct Payments — PromptPay + Crypto                          --}}
    {{-- ============================================================ --}}
    <form wire:submit="saveDirectPayments" class="flex flex-col gap-6">
        <section class="rounded-card border border-line bg-card p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <flux:heading size="lg">PromptPay</flux:heading>
                        @if ($vendor->acceptsPromptPay())
                            <flux:badge color="lime" size="sm" inset="top bottom">Aktiv</flux:badge>
                        @endif
                    </div>
                    <flux:text class="mt-2 text-muted text-caption">
                        Wir generieren einen QR-Code aus Deiner PromptPay-Nummer. Tourist scannt mit seiner Banking-App, Geld geht direkt an Dich.
                    </flux:text>
                </div>
            </div>

            <div class="mt-4">
                <flux:input
                    wire:model="promptpayPhone"
                    label="PromptPay Nummer"
                    placeholder="0812345678 oder +66812345678"
                    description="Thai-Mobilnummer oder 13-stellige National-ID"
                />
            </div>
        </section>

        <section class="rounded-card border border-line bg-card p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <flux:heading size="lg">Crypto</flux:heading>
                        @if ($vendor->acceptsStablecoin())
                            <flux:badge color="lime" size="sm" inset="top bottom">Aktiv</flux:badge>
                        @endif
                    </div>
                    <flux:text class="mt-2 text-muted text-caption">
                        Wallet-Adresse und Kette. Wir zeigen die Adresse mit Copy-Button, kein Wallet-Connect.
                    </flux:text>
                </div>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-3">
                <div class="md:col-span-2">
                    <flux:input
                        wire:model="stablecoinAddress"
                        label="Wallet-Adresse"
                        placeholder="0x... oder bc1..."
                    />
                </div>
                <flux:select wire:model="stablecoinChain" label="Kette" placeholder="Auswählen">
                    @foreach ($chains as $code => $label)
                        <flux:select.option value="{{ $code }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </section>

        <div class="flex justify-end">
            <flux:button type="submit" variant="primary">Speichern</flux:button>
        </div>
    </form>
</div>
