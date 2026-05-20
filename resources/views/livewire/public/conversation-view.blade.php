<div class="flex flex-col gap-4">
    <header class="space-y-1">
        <p class="text-caption uppercase tracking-wide text-muted">
            {{ __('Chat with :name', ['name' => $conversation->vendor->name]) }}
        </p>
        <p class="text-caption text-soft-muted">
            {{ __('Replies arrive in :locale — we translate both ways.', [
                'locale' => strtoupper($conversation->tourist_locale),
            ]) }}
        </p>
    </header>

    <section class="flex flex-col overflow-hidden rounded-lg border border-line bg-canvas">
        <div
            class="flex max-h-[60vh] min-h-[40vh] flex-col gap-3 overflow-y-auto px-5 py-5"
            wire:poll.5s
            role="log"
            aria-live="polite"
        >
            @forelse ($messages as $message)
                @php($isTourist = $message->sender === 'tourist')
                @php($display = $isTourist ? $message->original_text : ($message->translated_text ?? $message->original_text))
                <div @class([
                    'flex',
                    'justify-end' => $isTourist,
                    'justify-start' => ! $isTourist,
                ])>
                    <div @class([
                        'max-w-[85%] whitespace-pre-wrap rounded-lg px-4 py-3 text-body',
                        'bg-accent text-canvas' => $isTourist,
                        'border border-line bg-surface text-ink' => ! $isTourist,
                    ])>
                        {{ $display }}
                        @if (! $isTourist && $message->translated_text === null)
                            <p class="mt-1 font-mono text-[10px] uppercase tracking-wider text-soft-muted">
                                {{ __('Translating…') }}
                            </p>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-caption text-soft-muted">{{ __('No messages yet.') }}</p>
            @endforelse
        </div>

        <form wire:submit="sendMessage" class="flex items-end gap-2 border-t border-line bg-surface px-4 py-3">
            <textarea
                wire:model="draft"
                rows="1"
                placeholder="{{ __('Your message…') }}"
                class="flex-1 resize-none rounded-sm border border-line bg-canvas px-3 py-2 text-body text-ink placeholder:text-soft-muted focus:outline-none focus:ring-2 focus:ring-accent/30"
                x-on:keydown.enter.prevent="$el.form.requestSubmit()"
            ></textarea>

            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="sendMessage"
                class="rounded-full bg-accent px-4 py-2 text-caption font-medium text-canvas transition hover:opacity-90 disabled:opacity-50"
            >
                {{ __('Send') }}
            </button>
        </form>
    </section>
</div>
