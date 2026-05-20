<div class="space-y-6">
    <header>
        <flux:heading size="xl" level="1">{{ __('Brand') }}</flux:heading>
        <flux:text class="mt-1 text-muted">
            {{ __('Your source language and an optional accent color. Everything else (translations, payouts, payment options) follows from here.') }}
        </flux:text>
    </header>

    <flux:card>
        <flux:heading size="lg">{{ __('Language') }}</flux:heading>
        <flux:text class="mt-1 text-muted" size="sm">
            {{ __('You write your feed in this language. We translate it to the other two automatically. Switching here re-translates every component, usually 30–60 seconds with a queue worker running.') }}
        </flux:text>

        <form wire:submit="changeLocale" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
            <flux:field class="flex-1">
                <flux:label>{{ __('Source language') }}</flux:label>
                <flux:select wire:model="locale">
                    @foreach ($localeChoices as $choice)
                        <flux:select.option value="{{ $choice['value'] }}">{{ $choice['label'] }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>

            <flux:button type="submit" variant="primary" class="self-start">
                {{ __('Save & re-translate') }}
            </flux:button>
        </form>
    </flux:card>

    <flux:card>
        <flux:heading size="lg">{{ __('Presets') }}</flux:heading>
        <flux:text class="mt-1 text-muted" size="sm">
            {{ __('Quick pick, click to apply instantly.') }}
        </flux:text>

        <ul class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
            @foreach ($presets as $preset)
                @php($active = ($accentColor ?? '') === $preset['value'])
                <li>
                    <button
                        type="button"
                        wire:click="selectPreset('{{ $preset['value'] }}')"
                        @class([
                            'flex w-full items-center gap-3 rounded-lg border-[1.5px] px-4 py-3 text-left transition',
                            'border-ink' => $active,
                            'border-line hover:border-ink/40' => ! $active,
                        ])
                    >
                        @if ($preset['value'] === '')
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-[1.5px] border-line">
                                ●
                            </span>
                        @else
                            <span
                                class="h-8 w-8 shrink-0 rounded-full"
                                style="background-color: {{ $preset['value'] }};"
                                aria-hidden="true"
                            ></span>
                        @endif
                        <span class="text-label">{{ $preset['label'] }}</span>
                    </button>
                </li>
            @endforeach
        </ul>
    </flux:card>

    <flux:card>
        <flux:heading size="lg">{{ __('Custom hex color') }}</flux:heading>
        <flux:text class="mt-1 text-muted" size="sm">
            {{ __('Must be dark enough for white text on top. Format: #RRGGBB.') }}
        </flux:text>

        <form wire:submit="save" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
            <flux:field class="flex-1">
                <flux:label>{{ __('Hex') }}</flux:label>
                <flux:input
                    wire:model="accentColor"
                    placeholder="#0A0A0B"
                    class="!font-mono"
                />
                @error('accentColor')
                    <flux:error>{{ __('Please use the #RRGGBB format.') }}</flux:error>
                @enderror
            </flux:field>

            <flux:button type="submit" variant="primary" class="self-start">
                {{ __('Save') }}
            </flux:button>
        </form>

        @if ($savedAt)
            <flux:text size="sm" class="mt-3 text-muted">
                {{ __('Saved at') }} {{ $savedAt }}.
            </flux:text>
        @endif
    </flux:card>
</div>
