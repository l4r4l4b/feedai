<div class="mx-auto h-screen w-full max-w-2xl bg-canvas">
    <header class="border-b border-line bg-card px-5 py-4">
        <p class="font-mono text-mono-label uppercase text-muted">Posteingang</p>
        <p class="mt-1 text-caption text-soft-ink">
            Nachrichten von Touristen — automatisch übersetzt in Deine Sprache.
        </p>
    </header>

    <ul class="divide-y divide-line">
        @forelse ($conversations as $conversation)
            @php($last = $conversation->messages->first())
            @php($preview = $last?->sender === 'tourist'
                ? ($last->translated_text ?? $last->original_text)
                : ($last?->original_text ?? '—'))
            <li>
                <a
                    href="{{ route('inbox.show', ['conversation' => $conversation->token]) }}"
                    class="flex items-start gap-3 px-5 py-4 transition hover:bg-soft"
                >
                    <div class="flex-1">
                        <div class="flex items-baseline justify-between gap-3">
                            <span class="text-body text-ink">
                                {{ $conversation->tourist_name ?: 'Tourist' }}
                            </span>
                            <span class="font-mono text-mono-label uppercase text-muted">
                                {{ $conversation->last_message_at?->diffForHumans() }}
                            </span>
                        </div>
                        <p class="mt-1 line-clamp-2 text-caption text-muted">
                            {{ $preview }}
                        </p>
                    </div>
                </a>
            </li>
        @empty
            <li class="px-5 py-10 text-center text-caption text-muted">
                Noch keine Nachrichten.
            </li>
        @endforelse
    </ul>
</div>
