<div class="space-y-6">
    <header class="flex flex-wrap items-baseline justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">{{ __('Your feed') }}</flux:heading>
            <flux:text class="mt-1 text-muted">
                {{ __('Click any component to edit it — changes go live instantly.') }}
            </flux:text>
        </div>

        <div class="flex items-center gap-2">
            <flux:button :href="url('/'.$vendorSlug)" target="_blank" icon="arrow-top-right-on-square" variant="ghost" size="sm">
                {{ __('Open public view') }}
            </flux:button>
        </div>
    </header>

    @if ($editingType)
        <flux:card>
            <div class="flex items-baseline justify-between gap-4">
                <flux:heading size="lg">{{ $editingType }}</flux:heading>
                <flux:button wire:click="closeEditor" variant="ghost" size="sm">
                    {{ __('Close') }}
                </flux:button>
            </div>

            @if ($editError)
                <flux:callout variant="danger" class="mt-4" icon="exclamation-triangle">
                    {{ $editError }}
                </flux:callout>
            @endif

            <div class="mt-5 grid gap-4">
                <flux:field>
                    <flux:label>{{ __('Fields (YAML)') }}</flux:label>
                    <flux:textarea
                        wire:model.live.debounce.400ms="editingYaml"
                        rows="12"
                        class="!font-mono !text-[12px]"
                    >{{ $editingYaml }}</flux:textarea>
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Body (Markdown, optional)') }}</flux:label>
                    <flux:textarea
                        wire:model="editingBody"
                        rows="6"
                        class="!font-mono !text-[12px]"
                    >{{ $editingBody }}</flux:textarea>
                </flux:field>

                <flux:button wire:click="saveEditor" variant="primary" class="self-start">
                    {{ __('Save') }}
                </flux:button>
            </div>
        </flux:card>
    @else
        <flux:card>
            @if (empty($components))
                <flux:callout variant="secondary" icon="information-circle">
                    {{ __('No components active yet. Head back to the onboarding chat or use the AI assistant to add one.') }}
                </flux:callout>
            @else
                <ul class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach ($components as $component)
                        <li>
                            <button
                                type="button"
                                wire:click="openEditor('{{ $component['type'] }}')"
                                class="flex w-full items-center justify-between gap-3 py-3 text-left transition hover:opacity-70"
                            >
                                <span class="text-sm font-semibold">{{ $component['type'] }}</span>
                                <flux:icon name="chevron-right" class="size-4 text-zinc-500" />
                            </button>
                        </li>
                    @endforeach
                </ul>
            @endif
        </flux:card>
    @endif
</div>
