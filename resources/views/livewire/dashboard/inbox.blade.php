<div class="space-y-6" wire:poll.10s>
    <header>
        <flux:heading size="xl" level="1">{{ __('Inbox') }}</flux:heading>
        <flux:text class="mt-1 text-muted">
            {{ __('Tourist messages — automatically translated into your language.') }}
        </flux:text>
    </header>

    <flux:card class="p-0!">
        @forelse ($conversations as $conversation)
            @php($last = $conversation->messages->first())
            @php($preview = $last?->sender === 'tourist'
                ? ($last->translated_text ?? $last->original_text)
                : ($last?->original_text ?? '—'))
            @php($unread = (int) ($conversation->unread_count ?? 0))
            @php($touristInitial = mb_strtoupper(mb_substr($conversation->tourist_name ?: 'T', 0, 1)))
            <a
                href="{{ route('inbox.show', ['conversation' => $conversation->token]) }}"
                wire:navigate
                @class([
                    'flex items-start gap-4 border-b border-line px-5 py-4 transition last:border-b-0 hover:bg-surface',
                    'bg-surface/40' => $unread > 0,
                ])
            >
                <div @class([
                    'flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-label font-semibold',
                    'bg-ink text-canvas' => $unread > 0,
                    'bg-surface text-muted' => $unread === 0,
                ])>
                    {{ $touristInitial }}
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex items-baseline justify-between gap-3">
                        <span @class([
                            'truncate',
                            'font-bold text-text' => $unread > 0,
                            'font-semibold text-text' => $unread === 0,
                        ])>
                            {{ $conversation->tourist_name ?: __('Tourist') }}
                            <flux:badge size="sm" color="zinc" inset="top bottom" class="ml-1 align-middle">
                                {{ strtoupper($conversation->tourist_locale) }}
                            </flux:badge>
                        </span>
                        <span class="shrink-0 text-caption text-muted">
                            {{ $conversation->last_message_at?->diffForHumans() }}
                        </span>
                    </div>
                    <p @class([
                        'mt-1 line-clamp-2 text-caption',
                        'text-text' => $unread > 0,
                        'text-muted' => $unread === 0,
                    ])>
                        {{ $preview }}
                    </p>
                </div>

                @if ($unread > 0)
                    <flux:badge size="sm" color="lime" inset="top bottom" class="ml-1 mt-1 shrink-0">
                        {{ $unread }}
                    </flux:badge>
                @else
                    <flux:icon name="chevron-right" class="mt-2 size-4 shrink-0 text-muted" />
                @endif
            </a>
        @empty
            <flux:callout variant="secondary" icon="inbox" class="m-5">
                {{ __('No messages yet.') }}
            </flux:callout>
        @endforelse
    </flux:card>
</div>
