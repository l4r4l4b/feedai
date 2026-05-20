@php
    $statusColor = [
        'live' => 'lime',
        'draft' => 'zinc',
        'disabled' => 'red',
    ];
@endphp

<div class="flex flex-col gap-6 p-6 lg:p-8">
    <header class="flex flex-col gap-3">
        <div>
            <flux:heading size="xl">Vendors</flux:heading>
            <flux:text class="mt-1 text-muted">
                Alle Accounts mit Status und Quick-Preview.
            </flux:text>
        </div>

        <div class="flex flex-wrap gap-2">
            @foreach (['all' => 'Alle', 'live' => 'Live', 'draft' => 'Draft', 'disabled' => 'Disabled'] as $value => $label)
                <button type="button"
                        wire:click="setStatusFilter('{{ $value }}')"
                        class="rounded-full border px-3 py-1 text-caption transition
                               {{ $statusFilter === $value
                                  ? 'border-ink bg-ink text-canvas'
                                  : 'border-line bg-canvas text-ink hover:border-ink/30' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </header>

    <flux:table :paginate="$this->vendors">
        <flux:table.columns>
            <flux:table.column>Name</flux:table.column>
            <flux:table.column>Slug</flux:table.column>
            <flux:table.column>Owner</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Onboarding</flux:table.column>
            <flux:table.column>Preview</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->vendors as $vendor)
                <flux:table.row :key="$vendor->id">
                    <flux:table.cell variant="strong">{{ $vendor->name }}</flux:table.cell>
                    <flux:table.cell><span class="font-mono text-caption text-muted">{{ $vendor->slug }}</span></flux:table.cell>
                    <flux:table.cell>{{ $vendor->user->email ?? ', ' }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" :color="$statusColor[$vendor->status] ?? 'zinc'" inset="top bottom">
                            {{ $vendor->status }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap text-caption text-muted">
                        {{ $vendor->onboarding_completed_at?->diffForHumans() ?? ', ' }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:link :href="url('/'.$vendor->slug)" target="_blank">Open feed</flux:link>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6">
                        <span class="text-muted">No vendors match this filter.</span>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
