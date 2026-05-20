<div class="flex flex-col gap-6 p-6 lg:p-8">
    <header>
        <flux:heading size="xl">Admin</flux:heading>
        <flux:text class="mt-1 text-muted">
            Overview of every vendor, payment, and conversation on the platform.
        </flux:text>
    </header>

    <div class="grid gap-4 md:grid-cols-3">
        <a href="{{ route('admin.vendors') }}" wire:navigate
           class="block rounded-lg border border-line bg-canvas p-5 transition hover:border-ink/30">
            <div class="text-caption uppercase tracking-wide text-muted">Vendors</div>
            <div class="mt-2 flex items-baseline gap-3">
                <span class="text-hero font-semibold text-ink">{{ $this->stats['vendors_total'] }}</span>
                <span class="text-caption text-muted">
                    {{ $this->stats['vendors_live'] }} live · {{ $this->stats['vendors_draft'] }} draft
                </span>
            </div>
        </a>

        <a href="{{ route('admin.payments') }}" wire:navigate
           class="block rounded-lg border border-line bg-canvas p-5 transition hover:border-ink/30">
            <div class="text-caption uppercase tracking-wide text-muted">Payments</div>
            <div class="mt-2 flex items-baseline gap-3">
                <span class="text-hero font-semibold text-ink">{{ $this->stats['payments_total'] }}</span>
                <span class="text-caption text-muted">
                    {{ $this->stats['payments_paid'] }} paid
                </span>
            </div>
        </a>

        <a href="{{ route('admin.conversations') }}" wire:navigate
           class="block rounded-lg border border-line bg-canvas p-5 transition hover:border-ink/30">
            <div class="text-caption uppercase tracking-wide text-muted">Conversations</div>
            <div class="mt-2 flex items-baseline gap-3">
                <span class="text-hero font-semibold text-ink">{{ $this->stats['conversations_total'] }}</span>
            </div>
        </a>
    </div>
</div>
