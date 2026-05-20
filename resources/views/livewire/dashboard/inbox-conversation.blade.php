<div class="space-y-4">
    <header class="flex flex-wrap items-baseline justify-between gap-4">
        <div>
            <flux:button :href="route('inbox')" wire:navigate variant="ghost" size="sm" icon="arrow-left" class="mb-2">
                {{ __('Inbox') }}
            </flux:button>
            <flux:heading size="xl" level="1">
                {{ $conversation->tourist_name ?: __('Tourist') }}
            </flux:heading>
            <flux:text class="mt-1 text-muted">
                @if ($conversation->tourist_email)
                    {{ $conversation->tourist_email }} ·
                @endif
                {{ __('Language:') }} {{ strtoupper($conversation->tourist_locale) }}
                · {{ __('your replies are auto-translated') }}
            </flux:text>
        </div>
    </header>

    <flux:card class="p-0!">
        <div
            class="space-y-3 px-5 py-5"
            wire:poll.5s
            role="log"
            aria-live="polite"
        >
            @foreach ($messages as $message)
                @php($isVendor = $message->sender === 'vendor')
                @php($display = $isVendor ? $message->original_text : ($message->translated_text ?? $message->original_text))
                <div @class([
                    'flex',
                    'justify-end' => $isVendor,
                    'justify-start' => ! $isVendor,
                ])>
                    <div @class([
                        'max-w-[85%] whitespace-pre-wrap rounded-lg px-4 py-3 text-sm',
                        'rounded-br-sm bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' => $isVendor,
                        'rounded-bl-sm bg-zinc-100 text-zinc-900 dark:bg-zinc-700 dark:text-white' => ! $isVendor,
                    ])>
                        {{ $display }}
                        @if (! $isVendor && $message->translated_text === null)
                            <p class="mt-1 text-xs uppercase tracking-wider text-zinc-400">
                                {{ __('Translating…') }} ({{ strtoupper($message->original_locale) }}: {{ $message->original_text }})
                            </p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <form wire:submit="sendReply" class="flex items-end gap-2 border-t border-zinc-200 px-5 py-3 dark:border-zinc-700">
            <flux:textarea
                wire:model="draft"
                rows="1"
                :placeholder="__('Reply in').' '.strtoupper($conversation->vendor->locale ?? 'TH').'…'"
                class="flex-1"
                x-on:keydown.enter.prevent="$el.form.requestSubmit()"
            />
            <flux:button type="submit" variant="primary" icon="paper-airplane" wire:loading.attr="disabled" wire:target="sendReply">
                {{ __('Send') }}
            </flux:button>
        </form>
    </flux:card>
</div>
