@php
    $vendorInitial = mb_strtoupper(mb_substr($conversation->vendor->name ?: 'V', 0, 1));
    $touristInitial = mb_strtoupper(mb_substr($conversation->tourist_name ?: 'You', 0, 1));
    $localeNames = ['en' => __('English'), 'de' => __('Deutsch'), 'th' => __('ไทย')];
    $vendorLocale = $conversation->vendor->locale ?? 'th';
    $touristLocale = $conversation->tourist_locale;
    $vendorLangLabel = $localeNames[$vendorLocale] ?? strtoupper($vendorLocale);
    $touristLangLabel = $localeNames[$touristLocale] ?? strtoupper($touristLocale);
    $sameLocale = $vendorLocale === $touristLocale;
@endphp

<div class="flex flex-col gap-3">
    <header class="space-y-1">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-ink text-label font-semibold text-canvas">
                {{ $vendorInitial }}
            </div>
            <div class="min-w-0">
                <p class="text-caption uppercase tracking-wide text-muted">{{ __('Chat with') }}</p>
                <p class="truncate text-title text-ink">{{ $conversation->vendor->name }}</p>
            </div>
        </div>
        @if ($sameLocale)
            <p class="text-caption text-soft-muted">
                {{ __('You both speak :lang.', ['lang' => $vendorLangLabel]) }}
            </p>
        @else
            <p class="text-caption text-soft-muted">
                {{ __('You write in :tourist · :name reads in :vendor, we translate both ways.', [
                    'tourist' => $touristLangLabel,
                    'vendor' => $vendorLangLabel,
                    'name' => $conversation->vendor->name,
                ]) }}
            </p>
        @endif
    </header>

    <section class="flex flex-col overflow-hidden rounded-lg border border-line bg-canvas">
        <div
            class="flex max-h-[60vh] min-h-[40vh] flex-col gap-3 overflow-y-auto bg-surface px-4 py-4"
            wire:poll.4s
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
            @forelse ($messages as $message)
                @php($isTourist = $message->sender === 'tourist')
                @php($display = $isTourist ? $message->original_text : ($message->translated_text ?? $message->original_text))
                <div @class([
                    'flex items-end gap-2',
                    'justify-end' => $isTourist,
                    'justify-start' => ! $isTourist,
                ])>
                    @unless ($isTourist)
                        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-ink text-caption font-semibold text-canvas">
                            {{ $vendorInitial }}
                        </div>
                    @endunless

                    <div @class([
                        'max-w-[78%] flex flex-col gap-1',
                        'items-end' => $isTourist,
                        'items-start' => ! $isTourist,
                    ])>
                        <div @class([
                            'whitespace-pre-wrap rounded-2xl px-4 py-2.5 text-body',
                            'rounded-br-md bg-accent text-canvas' => $isTourist,
                            'rounded-bl-md bg-canvas text-ink border border-line' => ! $isTourist,
                        ])>{{ $display }}</div>

                        <div class="px-1 text-[10px] uppercase tracking-wider text-soft-muted">
                            {{ $message->created_at->format('H:i') }}
                            @if (! $isTourist && $message->translated_text === null)
                                · <span class="text-amber-600">{{ __('translating…') }}</span>
                            @endif
                        </div>
                    </div>

                    @if ($isTourist)
                        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-canvas text-caption font-semibold text-muted">
                            {{ $touristInitial }}
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-caption text-soft-muted">{{ __('No messages yet.') }}</p>
            @endforelse
        </div>

        <form wire:submit="sendMessage" class="flex items-end gap-2 border-t border-line bg-canvas px-4 py-3">
            <textarea
                wire:model="draft"
                rows="1"
                placeholder="{{ __('Your message…') }}"
                class="flex-1 resize-none rounded-md border border-line bg-surface px-3 py-2 text-body text-ink placeholder:text-soft-muted focus:outline-none focus:ring-2 focus:ring-accent/30"
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
