<div class="flex h-full flex-col">
    <ol class="flex-1 space-y-3 overflow-y-auto px-4 py-4" role="log" aria-live="polite">
        @foreach ($messages as $message)
            <li @class([
                'flex',
                'justify-end' => $message['role'] === 'user',
                'justify-start' => $message['role'] === 'assistant',
            ])>
                <div @class([
                    'max-w-[85%] whitespace-pre-wrap rounded-card px-4 py-3 text-body',
                    'bg-accent text-card' => $message['role'] === 'user',
                    'border border-line bg-card text-ink' => $message['role'] === 'assistant',
                ])>
                    {{ $message['text'] }}
                </div>
            </li>
        @endforeach

        <li wire:loading wire:target="sendMessage,photo" class="flex justify-start">
            <div class="rounded-card border border-line bg-card px-4 py-3 text-caption text-muted">
                …
            </div>
        </li>
    </ol>

    @if ($photo)
        <div class="flex items-center justify-between gap-3 border-t border-line bg-soft px-4 py-2 text-caption text-muted">
            <span>📎 Bild bereit zum Senden — {{ $photo->getClientOriginalName() }}</span>
            <button
                type="button"
                wire:click="$set('photo', null)"
                class="font-mono text-mono-label uppercase text-warm transition hover:text-ink"
            >
                Entfernen
            </button>
        </div>
    @endif

    <form
        wire:submit="sendMessage"
        class="flex items-end gap-2 border-t border-line bg-card px-4 py-3"
    >
        <label class="flex h-10 w-10 shrink-0 cursor-pointer items-center justify-center rounded-button border border-line bg-canvas text-ink transition hover:bg-soft">
            <input
                type="file"
                wire:model="photo"
                accept="image/jpeg,image/png,image/webp"
                class="sr-only"
            />
            <span aria-hidden="true">📎</span>
        </label>

        <textarea
            wire:model="draft"
            rows="1"
            placeholder="Schreib einfach drauflos…"
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
