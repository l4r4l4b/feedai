<div class="mx-auto flex h-screen w-full max-w-md flex-col bg-canvas md:max-w-2xl">
    <header class="border-b border-line bg-card px-5 py-4">
        <p class="font-mono text-mono-label uppercase text-muted">Chat mit {{ $conversation->vendor->name }}</p>
        <p class="mt-1 text-caption text-soft-ink">
            Antworten erscheinen automatisch in {{ strtoupper($conversation->tourist_locale) }} —
            der Vendor schreibt in seiner Sprache, wir übersetzen für Dich.
        </p>
    </header>

    <div
        class="flex-1 space-y-3 overflow-y-auto px-5 py-4"
        wire:poll.5s
        role="log"
        aria-live="polite"
    >
        @foreach ($messages as $message)
            @php($isTourist = $message->sender === 'tourist')
            @php($display = $isTourist ? $message->original_text : ($message->translated_text ?? $message->original_text))
            <div @class([
                'flex',
                'justify-end' => $isTourist,
                'justify-start' => ! $isTourist,
            ])>
                <div @class([
                    'max-w-[85%] whitespace-pre-wrap rounded-card px-4 py-3 text-body',
                    'bg-accent text-card' => $isTourist,
                    'border border-line bg-card text-ink' => ! $isTourist,
                ])>
                    {{ $display }}
                    @if (! $isTourist && $message->translated_text === null)
                        <p class="mt-1 font-mono text-[10px] uppercase tracking-wider text-soft-ink">
                            Übersetzung läuft…
                        </p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <form wire:submit="sendMessage" class="flex items-end gap-2 border-t border-line bg-card px-5 py-3">
        <textarea
            wire:model="draft"
            rows="1"
            placeholder="Deine Nachricht…"
            class="flex-1 resize-none rounded-input border border-line bg-canvas px-3 py-2 text-body text-ink placeholder:text-soft-ink focus:outline-none focus:ring-2 focus:ring-accent/30"
            x-on:keydown.enter.prevent="$el.form.requestSubmit()"
        ></textarea>

        <button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="sendMessage"
            class="rounded-button bg-accent px-4 py-2 text-caption font-medium text-card transition hover:opacity-90 disabled:opacity-50"
        >
            Senden
        </button>
    </form>
</div>
