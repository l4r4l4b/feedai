@php
    $touristInitial = mb_strtoupper(mb_substr($conversation->tourist_name ?: 'T', 0, 1));
    $vendorInitial = mb_strtoupper(mb_substr($conversation->vendor->name ?: 'V', 0, 1));
    $localeNames = ['en' => __('English'), 'de' => __('Deutsch'), 'th' => __('ไทย')];
    $vendorLocale = $conversation->vendor->locale ?? 'th';
    $touristLocale = $conversation->tourist_locale;
    $vendorLangLabel = $localeNames[$vendorLocale] ?? strtoupper($vendorLocale);
    $touristLangLabel = $localeNames[$touristLocale] ?? strtoupper($touristLocale);
    $sameLocale = $vendorLocale === $touristLocale;
@endphp

<div class="-m-6 flex h-[calc(100vh-3.5rem)] flex-col overflow-hidden lg:-m-8 lg:h-screen">
    {{-- Header --}}
    <header class="shrink-0 border-b border-line bg-canvas px-5 py-3">
        <flux:button :href="route('inbox')" wire:navigate variant="ghost" size="xs" icon="arrow-left" class="mb-2">
            {{ __('Inbox') }}
        </flux:button>
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-surface text-label font-semibold text-text">
                {{ $touristInitial }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-label text-text">
                    {{ $conversation->tourist_name ?: __('Tourist') }}
                    <flux:badge size="sm" color="zinc" inset="top bottom" class="ml-1">
                        {{ strtoupper($conversation->tourist_locale) }}
                    </flux:badge>
                </p>
                <p class="truncate text-caption text-muted">
                    @if ($conversation->tourist_email)
                        {{ $conversation->tourist_email }} ·
                    @endif
                    @if ($sameLocale)
                        {{ __('You both speak :lang.', ['lang' => $vendorLangLabel]) }}
                    @else
                        {{ __('Guest writes in :tourist, we translate to your :vendor.', [
                            'tourist' => $touristLangLabel,
                            'vendor' => $vendorLangLabel,
                        ]) }}
                    @endif
                </p>
            </div>
        </div>
    </header>

    {{-- Message log, flex-1 fills, internal scroll --}}
    <div
        wire:poll.4s
        class="min-h-0 flex-1 overflow-y-auto bg-surface px-5 py-5"
        role="log"
        aria-live="polite"
        x-data="{
            scrollDown() {
                this.$nextTick(() => { this.$el.scrollTop = this.$el.scrollHeight; });
            }
        }"
        x-init="scrollDown()"
        x-on:livewire:update.window="scrollDown()"
    >
        <ul class="flex flex-col gap-3">
            @foreach ($messages as $message)
                @php($isVendor = $message->sender === 'vendor')
                @php($display = $isVendor ? $message->original_text : ($message->translated_text ?? $message->original_text))
                <li @class([
                    'flex items-end gap-2',
                    'justify-end' => $isVendor,
                    'justify-start' => ! $isVendor,
                ])>
                    @unless ($isVendor)
                        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-canvas text-caption font-semibold text-muted">
                            {{ $touristInitial }}
                        </div>
                    @endunless

                    <div @class([
                        'max-w-[78%] flex flex-col gap-1',
                        'items-end' => $isVendor,
                        'items-start' => ! $isVendor,
                    ])>
                        <div @class([
                            'whitespace-pre-wrap rounded-2xl px-4 py-2.5 text-body',
                            'rounded-br-md bg-ink text-canvas' => $isVendor,
                            'rounded-bl-md bg-canvas text-text border border-line' => ! $isVendor,
                        ])>{{ $display }}</div>

                        <div class="flex items-center gap-2 px-1 text-[10px] uppercase tracking-wider text-muted">
                            <span>{{ $message->created_at->format('H:i') }}</span>
                            @if (! $isVendor && $message->translated_text === null)
                                <span class="text-amber-600">{{ __('translating…') }}</span>
                            @endif
                        </div>
                    </div>

                    @if ($isVendor)
                        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-ink text-caption font-semibold text-canvas">
                            {{ $vendorInitial }}
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>

    {{-- Compose bar --}}
    <form wire:submit="sendReply" class="shrink-0 border-t border-line bg-canvas px-4 py-3">
        <div class="flex items-end gap-2">
            <flux:textarea
                wire:model="draft"
                rows="1"
                :placeholder="__('Reply in :lang…', ['lang' => $vendorLangLabel])"
                class="flex-1"
                x-on:keydown.enter.prevent="$el.form.requestSubmit()"
            />
            <flux:button
                type="submit"
                variant="primary"
                icon="paper-airplane"
                wire:loading.attr="disabled"
                wire:target="sendReply"
            >
                {{ __('Send') }}
            </flux:button>
        </div>
        @unless ($sameLocale)
            <p class="mt-2 text-caption text-muted">
                {{ __('You write in :vendor, guest reads in :tourist.', [
                    'vendor' => $vendorLangLabel,
                    'tourist' => $touristLangLabel,
                ]) }}
            </p>
        @endunless
    </form>
</div>
