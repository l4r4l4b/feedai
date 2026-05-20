<div class="flex h-screen flex-col md:flex-row">
    <section
        class="relative flex h-1/2 w-full flex-col border-b border-line bg-soft md:h-full md:w-3/5 md:border-b-0 md:border-r"
        x-data="{ refresh() { $refs.frame.contentWindow?.location.reload(); } }"
        x-on:feed-updated.window="refresh()"
    >
        <header class="flex items-center justify-between border-b border-line bg-card px-4 py-3">
            <p class="font-mono text-mono-label uppercase text-muted">Live Preview</p>
            <button
                type="button"
                x-on:click="refresh()"
                class="font-mono text-mono-label uppercase text-warm transition hover:text-ink"
            >
                Neu laden
            </button>
        </header>

        <iframe
            x-ref="frame"
            src="{{ url('/'.$vendorSlug) }}"
            title="Feed Preview"
            class="h-full w-full flex-1 bg-canvas"
        ></iframe>
    </section>

    <aside class="flex h-1/2 w-full flex-col bg-card md:h-full md:w-2/5">
        <header class="border-b border-line px-4 py-3">
            <p class="font-mono text-mono-label uppercase text-muted">FeedAI Onboarding</p>
            <p class="mt-0.5 text-caption text-soft-ink">
                Erzähl mir von Deinem Business — ich baue Deinen Feed dabei live auf.
            </p>
        </header>

        <livewire:onboarding.chat />
    </aside>
</div>
