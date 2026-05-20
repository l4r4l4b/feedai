<div>
{{-- Global form drawer — opens via postMessage from iframe skeletons or
     from the components list button below. --}}
<livewire:feed.component-drawer />

{{-- Manual YAML editor — overlay above the split when $editingType is set.
     The chat handles 99% of edits; this is a power-user escape hatch. --}}
@if ($editingType)
    <div class="fixed inset-0 z-40 flex items-start justify-center overflow-y-auto bg-black/40 px-4 py-10">
        <flux:card class="w-full max-w-3xl">
            <div class="flex items-baseline justify-between gap-4">
                <flux:heading size="lg">{{ $editingType }} — {{ __('manual edit') }}</flux:heading>
                <flux:button wire:click="closeEditor" variant="ghost" size="sm">
                    {{ __('Close') }}
                </flux:button>
            </div>

            @if ($editError)
                <flux:callout variant="danger" class="mt-4" icon="exclamation-triangle">
                    {{ $editError }}
                </flux:callout>
            @endif

            <div class="mt-4 grid gap-3 lg:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Fields (YAML)') }}</flux:label>
                    <flux:textarea
                        wire:model.live.debounce.400ms="editingYaml"
                        rows="10"
                        class="!font-mono !text-[12px]"
                    >{{ $editingYaml }}</flux:textarea>
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Body (Markdown, optional)') }}</flux:label>
                    <flux:textarea
                        wire:model="editingBody"
                        rows="10"
                        class="!font-mono !text-[12px]"
                    >{{ $editingBody }}</flux:textarea>
                </flux:field>
            </div>

            <flux:button wire:click="saveEditor" variant="primary" class="mt-3">
                {{ __('Save') }}
            </flux:button>
        </flux:card>
    </div>
@endif

{{-- Split view — same optics as the onboarding page (iframe left, chat right).
     Negative margins cancel the Flux `[grid-area:main]` padding so the split
     reaches the viewport edges. Height subtracts the 56px mobile header
     (Flux only shows it below lg); on lg+ we get the full viewport. --}}
<div class="-m-6 flex h-[calc(100vh-3.5rem)] flex-col overflow-hidden md:flex-row lg:-m-8 lg:h-screen">
    <section
        class="relative flex h-1/2 w-full min-h-0 flex-col border-b border-line bg-surface md:h-full md:w-3/5 md:border-b-0 md:border-r"
        x-data="{ refresh() { $refs.frame.contentWindow?.location.reload(); } }"
        x-on:feed-updated.window="refresh()"
    >
        <header class="shrink-0 flex items-center justify-between border-b border-line bg-canvas px-4 py-3">
            <p class="text-caption uppercase tracking-wide text-muted">Live preview</p>
            <div class="flex items-center gap-4">
                <button
                    type="button"
                    x-on:click="refresh()"
                    class="text-label text-text underline"
                >
                    Reload
                </button>
                <a
                    href="{{ url('/'.$vendorSlug) }}"
                    target="_blank"
                    class="text-label text-text underline"
                >
                    Open
                </a>
            </div>
        </header>

        <iframe
            x-ref="frame"
            src="{{ url('/'.$vendorSlug.'?builder=1') }}"
            title="Feed preview"
            class="min-h-0 w-full flex-1 bg-canvas"
        ></iframe>

    </section>

    <aside class="flex h-1/2 w-full min-h-0 flex-col bg-canvas md:h-full md:w-2/5">
        <header class="shrink-0 border-b border-line px-4 py-3">
            <p class="text-caption uppercase tracking-wide text-muted">Edit assistant</p>
            <p class="mt-1 text-caption text-soft-muted">
                Describe what to change — I update the feed live.
            </p>
        </header>

        <div class="flex-1 min-h-0 overflow-hidden">
            <livewire:dashboard.feed-chat />
        </div>
    </aside>
</div>
</div>
