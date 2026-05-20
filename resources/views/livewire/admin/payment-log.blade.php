@php
    $statusColor = [
        'paid' => 'lime',
        'pending' => 'amber',
        'failed' => 'red',
        'refunded' => 'zinc',
    ];
    $methodLabel = [
        'stripe' => 'Stripe',
        'promptpay' => 'PromptPay',
        'stablecoin' => 'Stablecoin',
    ];
@endphp

<div class="flex flex-col gap-6 p-6 lg:p-8">
    <header>
        <flux:heading size="xl">Payment Log</flux:heading>
        <flux:text class="mt-1 text-muted">
            Read-only Übersicht. Webhooks von Stripe aktualisieren den Status automatisch.
        </flux:text>
    </header>

    <flux:table :paginate="$this->payments">
        <flux:table.columns>
            <flux:table.column>Vendor</flux:table.column>
            <flux:table.column>Beschreibung</flux:table.column>
            <flux:table.column>Betrag</flux:table.column>
            <flux:table.column>Methode</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Reference</flux:table.column>
            <flux:table.column>Datum</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->payments as $payment)
                <flux:table.row :key="$payment->id">
                    <flux:table.cell>
                        <flux:link :href="url('/'.$payment->vendor->slug)" target="_blank">
                            {{ $payment->vendor->name }}
                        </flux:link>
                    </flux:table.cell>
                    <flux:table.cell>{{ $payment->description ?? '—' }}</flux:table.cell>
                    <flux:table.cell variant="strong" class="whitespace-nowrap">
                        {{ number_format($payment->amount_cents / 100, 2) }} {{ $payment->currency }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <span class="font-mono text-caption">{{ $methodLabel[$payment->method] ?? $payment->method }}</span>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" :color="$statusColor[$payment->status] ?? 'zinc'" inset="top bottom">
                            {{ $payment->status }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="font-mono text-caption text-muted">
                        {{ $payment->provider_reference ? \Illuminate\Support\Str::limit($payment->provider_reference, 18) : '—' }}
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap text-caption text-muted">
                        {{ ($payment->paid_at ?? $payment->created_at)->diffForHumans() }}
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="7">
                        <span class="text-muted">Noch keine Payments.</span>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
