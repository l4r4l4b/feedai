<div class="mx-auto max-w-3xl space-y-8">
    <header class="space-y-3 text-center">
        <flux:badge color="lime" size="lg" icon="check-circle">
            {{ __('Feed is live') }}
        </flux:badge>

        <flux:heading size="xl" level="1">
            {{ __(':name is online.', ['name' => $vendorName]) }}
        </flux:heading>

        <flux:text class="text-muted">
            {{ __('Three quick next steps, then the place runs itself.') }}
        </flux:text>
    </header>

    <div class="grid gap-4">
        {{-- Step 1 — Payment (Primary) --}}
        <flux:card class="relative overflow-hidden">
            <div class="absolute inset-y-0 left-0 w-1 bg-zinc-900 dark:bg-white"></div>
            <div class="flex flex-wrap items-start justify-between gap-4 pl-3">
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <flux:badge size="sm">1</flux:badge>
                        <flux:heading size="lg">{{ __('Set up payment methods') }}</flux:heading>
                    </div>
                    <flux:text class="text-muted">
                        {{ __('Stripe for cards, PromptPay for local guests, stablecoin optional. Without an active method you cannot accept bookings.') }}
                    </flux:text>
                </div>
                <flux:button
                    :href="route('dashboard.payments.settings')"
                    wire:navigate
                    variant="primary"
                    icon-trailing="arrow-right"
                >
                    {{ __('Set up now') }}
                </flux:button>
            </div>
        </flux:card>

        {{-- Step 2 — Inbox test --}}
        <flux:card>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <flux:badge size="sm" color="zinc">2</flux:badge>
                        <flux:heading size="lg">{{ __('Test the inbox') }}</flux:heading>
                    </div>
                    <flux:text class="text-muted">
                        {{ __('Send yourself a message through your public feed. See how it lands for real guests — and reply right back.') }}
                    </flux:text>
                </div>
                <flux:button
                    :href="route('public.contact', ['vendor' => $vendorSlug])"
                    target="_blank"
                    icon-trailing="arrow-top-right-on-square"
                    variant="ghost"
                >
                    {{ __('Send a test message') }}
                </flux:button>
            </div>
        </flux:card>

        {{-- Step 3 — Public feed --}}
        <flux:card>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <flux:badge size="sm" color="zinc">3</flux:badge>
                        <flux:heading size="lg">{{ __('Share the feed') }}</flux:heading>
                    </div>
                    <flux:text class="text-muted">
                        {{ __('Check your public feed and share the link on Instagram, WhatsApp statuses or a QR code at the stall.') }}
                    </flux:text>
                </div>
                <flux:button
                    :href="url('/'.$vendorSlug)"
                    target="_blank"
                    icon-trailing="arrow-top-right-on-square"
                    variant="ghost"
                >
                    {{ __('Open feed') }}
                </flux:button>
            </div>
        </flux:card>
    </div>

    <div class="text-center">
        <flux:button :href="route('dashboard')" wire:navigate variant="ghost" size="sm">
            {{ __('Later — to dashboard') }}
        </flux:button>
    </div>
</div>
