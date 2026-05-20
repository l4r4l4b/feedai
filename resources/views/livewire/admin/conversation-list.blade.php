<div class="flex flex-col gap-6 p-6 lg:p-8">
    <header>
        <flux:heading size="xl">Conversations</flux:heading>
        <flux:text class="mt-1 text-muted">
            Read-only moderation view. Each message stores both the original language and the translation.
        </flux:text>
    </header>

    <flux:table :paginate="$this->conversations">
        <flux:table.columns>
            <flux:table.column>Vendor</flux:table.column>
            <flux:table.column>Tourist</flux:table.column>
            <flux:table.column>Language</flux:table.column>
            <flux:table.column>Messages</flux:table.column>
            <flux:table.column>Last activity</flux:table.column>
            <flux:table.column>Token</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->conversations as $conversation)
                <flux:table.row :key="$conversation->id">
                    <flux:table.cell variant="strong">
                        <flux:link :href="url('/'.$conversation->vendor->slug)" target="_blank">
                            {{ $conversation->vendor->name }}
                        </flux:link>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="leading-tight">
                            <div>{{ $conversation->tourist_name ?? ', ' }}</div>
                            <div class="text-caption text-muted">{{ $conversation->tourist_email ?? '' }}</div>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" color="zinc" inset="top bottom">{{ strtoupper($conversation->tourist_locale) }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell variant="strong">{{ $conversation->messages_count }}</flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap text-caption text-muted">
                        {{ $conversation->last_message_at?->diffForHumans() ?? ', ' }}
                    </flux:table.cell>
                    <flux:table.cell class="font-mono text-caption text-muted">
                        {{ \Illuminate\Support\Str::limit($conversation->token, 12, '…') }}
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6">
                        <span class="text-muted">No conversations yet.</span>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
