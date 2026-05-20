<div class="flex h-screen flex-col md:flex-row">
    <section
        class="relative flex h-1/2 w-full flex-col border-b border-line bg-surface md:h-full md:w-3/5 md:border-b-0 md:border-r"
        x-data="{ refresh() { $refs.frame.contentWindow?.location.reload(); } }"
        x-on:feed-updated.window="refresh()"
    >
        <header class="flex items-center justify-between border-b border-line bg-canvas px-4 py-3">
            <p class="text-caption uppercase tracking-wide text-muted">Live Preview</p>
            <button
                type="button"
                x-on:click="refresh()"
                class="text-label text-text underline"
            >
                Reload
            </button>
        </header>

        <iframe
            x-ref="frame"
            src="{{ url('/'.$vendorSlug) }}"
            title="Feed Preview"
            class="h-full w-full flex-1 bg-canvas"
        ></iframe>
    </section>

    <aside class="flex h-1/2 w-full flex-col bg-canvas md:h-full md:w-2/5">
        <header class="border-b border-line px-4 py-3">
            <p class="text-caption uppercase tracking-wide text-muted">FeedAI Onboarding</p>
            <p class="mt-1 text-caption text-soft-muted">
                Tell me about your business — I'll build the feed live as we go.
            </p>
        </header>

        <livewire:onboarding.chat />
    </aside>
</div>
