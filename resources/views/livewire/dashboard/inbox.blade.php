<div class="space-y-6">
    <header>
        <flux:heading size="xl" level="1">{{ __('Inbox') }}</flux:heading>
        <flux:text class="mt-1 text-muted">
            {{ __('Tourist messages — automatically translated into your language.') }}
        </flux:text>
    </header>

    <flux:card>
        @forelse ($conversations as $conversation)
            @php($last = $conversation->messages->first())
            @php($preview = $last?->sender === 'tourist'
                ? ($last->translated_text ?? $last->original_text)
                : ($last?->original_text ?? '—'))
            <a
                href="{{ route('inbox.show', ['conversation' => $conversation->token]) }}"
                wire:navigate
                class="flex items-start gap-4 border-b border-zinc-200 py-4 transition last:border-b-0 hover:opacity-70 dark:border-zinc-700"
            >
                <div class="flex-1">
                    <div class="flex items-baseline justify-between gap-3">
                        <span class="font-semibold">
                            {{ $conversation->tourist_name ?: __('Tourist') }}
                        </span>
                        <flux:text size="xs" class="text-muted">
                            {{ $conversation->last_message_at?->diffForHumans() }}
                        </flux:text>
                    </div>
                    <flux:text size="sm" class="mt-1 line-clamp-2 text-muted">
                        {{ $preview }}
                    </flux:text>
                </div>
                <flux:icon name="chevron-right" class="mt-1 size-4 text-zinc-500" />
            </a>
        @empty
            <flux:callout variant="secondary" icon="inbox">
                {{ __('No messages yet.') }}
            </flux:callout>
        @endforelse
    </flux:card>
</div>
