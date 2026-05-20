<div class="flex h-[calc(100vh-4rem)] flex-col">
    {{-- Manual YAML editor — only visible when openEditor was called.
         Lives above the split view so the chat + preview stay accessible. --}}
    @if ($editingType)
        <flux:card class="mx-4 mt-4 mb-2">
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
    @endif

    {{-- Split view: live preview left, edit chat right. --}}
    <div class="flex min-h-0 flex-1 flex-col gap-4 px-4 py-4 lg:flex-row">
        {{-- Preview pane --}}
        <section
            class="flex min-h-0 flex-1 flex-col overflow-hidden rounded-lg border border-line bg-surface lg:basis-3/5"
            x-data="{ refresh() { $refs.frame?.contentWindow?.location.reload(); } }"
            x-on:feed-updated.window="refresh()"
        >
            <header class="flex items-center justify-between gap-3 border-b border-line bg-canvas px-4 py-2">
                <div class="flex items-baseline gap-3">
                    <flux:heading size="sm">{{ __('Live preview') }}</flux:heading>
                    <flux:text size="xs" class="text-muted">/{{ $vendorSlug }}</flux:text>
                </div>
                <div class="flex items-center gap-2">
                    <flux:button
                        x-on:click="refresh()"
                        size="xs"
                        variant="ghost"
                        icon="arrow-path"
                    >
                        {{ __('Reload') }}
                    </flux:button>
                    <flux:button
                        :href="url('/'.$vendorSlug)"
                        target="_blank"
                        size="xs"
                        variant="ghost"
                        icon="arrow-top-right-on-square"
                    >
                        {{ __('Open') }}
                    </flux:button>
                </div>
            </header>

            <iframe
                x-ref="frame"
                src="{{ url('/'.$vendorSlug) }}"
                title="Feed preview"
                class="min-h-0 w-full flex-1 bg-canvas"
            ></iframe>

            {{-- Components list — keeps manual editing reachable for power users.
                 Tests assert component types render here. --}}
            <details class="border-t border-line bg-canvas px-4 py-2">
                <summary class="cursor-pointer text-caption text-muted">
                    {{ __('Components') }} ({{ count($components) }})
                </summary>
                @if (empty($components))
                    <p class="mt-2 text-caption text-muted">
                        {{ __('No components yet — ask the chat to add something.') }}
                    </p>
                @else
                    <ul class="mt-2 divide-y divide-line">
                        @foreach ($components as $component)
                            <li>
                                <button
                                    type="button"
                                    wire:click="openEditor('{{ $component['type'] }}')"
                                    class="flex w-full items-center justify-between gap-3 py-2 text-left text-caption transition hover:opacity-70"
                                >
                                    <span class="font-mono">{{ $component['type'] }}</span>
                                    <flux:icon name="pencil-square" class="size-4 text-muted" />
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </details>
        </section>

        {{-- Chat pane --}}
        <aside class="flex min-h-0 flex-col overflow-hidden rounded-lg border border-line bg-canvas lg:basis-2/5">
            <header class="border-b border-line bg-surface px-4 py-2">
                <flux:heading size="sm">{{ __('Edit assistant') }}</flux:heading>
                <flux:text size="xs" class="text-muted">
                    {{ __('Describe what to change — I update the feed live.') }}
                </flux:text>
            </header>

            <div class="min-h-0 flex-1">
                <livewire:dashboard.feed-chat />
            </div>
        </aside>
    </div>
</div>
