<div class="mx-auto flex h-screen w-full max-w-2xl flex-col bg-canvas">
    <header class="flex items-baseline justify-between gap-3 border-b border-line bg-card px-5 py-4">
        <div>
            <p class="font-mono text-mono-label uppercase text-muted">
                {{ $conversation->tourist_name ?: 'Tourist' }}
                @if ($conversation->tourist_email)
                    · {{ $conversation->tourist_email }}
                @endif
            </p>
            <p class="mt-1 text-caption text-soft-ink">
                Tourist-Sprache: {{ strtoupper($conversation->tourist_locale) }} —
                Deine Antworten werden automatisch übersetzt.
            </p>
        </div>
        <a
            href="{{ route('inbox') }}"
            class="font-mono text-mono-label uppercase text-warm transition hover:text-ink"
        >
            ← Posteingang
        </a>
    </header>

    <div
        class="flex-1 space-y-3 overflow-y-auto px-5 py-4"
        wire:poll.5s
        role="log"
        aria-live="polite"
    >
        @foreach ($messages as $message)
            @php($isVendor = $message->sender === 'vendor')
            @php($display = $isVendor ? $message->original_text : ($message->translated_text ?? $message->original_text))
            <div @class([
                'flex',
                'justify-end' => $isVendor,
                'justify-start' => ! $isVendor,
            ])>
                <div @class([
                    'max-w-[85%] whitespace-pre-wrap rounded-card px-4 py-3 text-body',
                    'bg-accent text-card' => $isVendor,
                    'border border-line bg-card text-ink' => ! $isVendor,
                ])>
                    {{ $display }}
                    @if (! $isVendor && $message->translated_text === null)
                        <p class="mt-1 font-mono text-[10px] uppercase tracking-wider text-soft-ink">
                            Übersetzung läuft… ({{ strtoupper($message->original_locale) }} Original: {{ $message->original_text }})
                        </p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <form wire:submit="sendReply" class="flex items-end gap-2 border-t border-line bg-card px-5 py-3">
        <textarea
            wire:model="draft"
            rows="1"
            placeholder="Antworte auf {{ strtoupper($conversation->vendor->locale ?? 'TH') }}…"
            class="flex-1 resize-none rounded-input border border-line bg-canvas px-3 py-2 text-body text-ink placeholder:text-soft-ink focus:outline-none focus:ring-2 focus:ring-accent/30"
            x-on:keydown.enter.prevent="$el.form.requestSubmit()"
        ></textarea>

        <button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="sendReply"
            class="rounded-button bg-accent px-4 py-2 text-caption font-medium text-card transition hover:opacity-90 disabled:opacity-50"
        >
            Senden
        </button>
    </form>
</div>
