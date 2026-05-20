<div class="flex h-screen flex-col md:flex-row">
    <section
        class="relative flex h-1/2 w-full flex-col border-b border-line bg-soft md:h-full md:w-3/5 md:border-b-0 md:border-r"
        x-data="{ refresh() { $refs.frame.contentWindow?.location.reload(); } }"
        x-on:feed-updated.window="refresh()"
    >
        <header class="flex items-center justify-between border-b border-line bg-card px-4 py-3">
            <p class="font-mono text-mono-label uppercase text-muted">Dein Feed — Live</p>
            <a
                href="{{ url('/'.$vendorSlug) }}"
                target="_blank"
                rel="noopener"
                class="font-mono text-mono-label uppercase text-warm transition hover:text-ink"
            >
                Öffentlich ↗
            </a>
        </header>

        <iframe
            x-ref="frame"
            src="{{ url('/'.$vendorSlug) }}"
            title="Vendor Feed Preview"
            class="h-full w-full flex-1 bg-canvas"
        ></iframe>
    </section>

    <aside class="flex h-1/2 w-full flex-col bg-card md:h-full md:w-2/5">
        <header class="border-b border-line px-4 py-3">
            <p class="font-mono text-mono-label uppercase text-muted">Komponenten</p>
            <p class="mt-0.5 text-caption text-soft-ink">
                Klick auf eine Komponente, um sie zu bearbeiten.
            </p>
        </header>

        @if ($editingType)
            <div class="flex flex-1 flex-col gap-3 overflow-y-auto px-4 py-4">
                <div class="flex items-baseline justify-between">
                    <h2 class="text-section text-ink">{{ $editingType }}</h2>
                    <button
                        type="button"
                        wire:click="closeEditor"
                        class="font-mono text-mono-label uppercase text-warm transition hover:text-ink"
                    >
                        Schließen
                    </button>
                </div>

                @if ($editError)
                    <p class="rounded-input border border-error/40 bg-error/10 px-3 py-2 text-caption text-error">
                        {{ $editError }}
                    </p>
                @endif

                <label class="flex flex-col gap-1 text-caption text-muted">
                    Felder (YAML)
                    <textarea
                        wire:model.live.debounce.400ms="editingYaml"
                        rows="10"
                        class="resize-y rounded-input border border-line bg-canvas px-3 py-2 font-mono text-[12px] text-ink focus:outline-none focus:ring-2 focus:ring-accent/30"
                    >{{ $editingYaml }}</textarea>
                </label>

                <label class="flex flex-col gap-1 text-caption text-muted">
                    Body (Markdown, optional)
                    <textarea
                        wire:model="editingBody"
                        rows="6"
                        class="resize-y rounded-input border border-line bg-canvas px-3 py-2 font-mono text-[12px] text-ink focus:outline-none focus:ring-2 focus:ring-accent/30"
                    >{{ $editingBody }}</textarea>
                </label>

                <button
                    type="button"
                    wire:click="saveEditor"
                    class="self-start rounded-button bg-accent px-4 py-2 text-caption font-medium text-card transition hover:opacity-90"
                >
                    Speichern
                </button>
            </div>
        @else
            <ul class="flex-1 divide-y divide-line overflow-y-auto">
                @forelse ($components as $component)
                    <li>
                        <button
                            type="button"
                            wire:click="openEditor('{{ $component['type'] }}')"
                            class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left transition hover:bg-soft"
                        >
                            <span class="text-body text-ink">{{ $component['type'] }}</span>
                            <span class="font-mono text-mono-label uppercase text-muted">Bearbeiten</span>
                        </button>
                    </li>
                @empty
                    <li class="px-4 py-6 text-caption text-muted">
                        Noch keine Komponenten aktiv. Geh zurück zum Onboarding-Chat.
                    </li>
                @endforelse
            </ul>
        @endif
    </aside>
</div>
