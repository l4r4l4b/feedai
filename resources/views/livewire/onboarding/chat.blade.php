<div
    class="flex h-full flex-col"
    x-data="{
        scrollDown() {
            this.$nextTick(() => {
                const ol = this.$refs.messages;
                if (ol) ol.scrollTop = ol.scrollHeight;
            });
        },
        init() {
            this.scrollDown();
            // Edit-in-chat marker in the preview iframe postMessages here;
            // forward as a Livewire event so the agent picks it up.
            window.addEventListener('message', (event) => {
                if (event.data?.type === 'feedai:edit-in-chat' && typeof event.data.component === 'string') {
                    Livewire.dispatch('prefill-chat-draft', { component: event.data.component });
                }
            });
        }
    }"
    x-on:chat-scroll.window="scrollDown()"
>
    <ol
        x-ref="messages"
        class="flex-1 space-y-3 overflow-y-auto px-4 py-4"
        role="log"
        aria-live="polite"
    >
        {{-- Static greeting card --}}
        <li class="flex justify-start" wire:key="greeting">
            <div class="max-w-[85%] space-y-2 rounded-md rounded-bl-sm bg-surface px-4 py-3 text-body text-text">
                <p>Hi{{ $vendorName !== '' ? ' '.$vendorName : '' }}!</p>
                <p>Good to meet you. Tell me briefly — what do you do, and where?</p>
                <p>You can attach photos right here — multiple at once works too.</p>
            </div>
        </li>

        {{-- Persisted DB messages --}}
        @foreach ($chatMessages as $message)
            <li
                wire:key="msg-{{ $message['id'] }}"
                @class([
                    'flex',
                    'justify-end' => $message['role'] === 'user',
                    'justify-start' => $message['role'] === 'assistant',
                ])
            >
                <div @class([
                    'max-w-[85%] flex flex-col gap-2',
                    'items-end' => $message['role'] === 'user',
                    'items-start' => $message['role'] === 'assistant',
                ])>
                    @if (! empty($message['image_urls']))
                        <div class="flex flex-wrap gap-2">
                            @foreach ($message['image_urls'] as $url)
                                <img
                                    src="{{ $url }}"
                                    alt=""
                                    loading="lazy"
                                    class="max-h-40 max-w-full rounded-md object-cover"
                                />
                            @endforeach
                        </div>
                    @endif

                    @if ($message['text'] !== '')
                        <div @class([
                            'whitespace-pre-wrap rounded-md px-4 py-3 text-body',
                            'rounded-br-sm bg-accent text-canvas' => $message['role'] === 'user',
                            'rounded-bl-sm bg-surface text-text' => $message['role'] === 'assistant',
                        ])>{{ $message['text'] }}</div>
                    @endif

                    @if ($message['tool_summary'] !== null)
                        <p class="text-caption text-muted">{{ $message['tool_summary'] }}</p>
                    @endif
                </div>
            </li>
        @endforeach

        {{-- Optimistic user bubble: shown between submitPrompt and runAgent --}}
        @if ($pendingText !== '' || ! empty($pendingImageUrls))
            <li class="flex justify-end" wire:key="pending">
                <div class="max-w-[85%] flex flex-col items-end gap-2">
                    @if (! empty($pendingImageUrls))
                        <div class="flex flex-wrap gap-2">
                            @foreach ($pendingImageUrls as $url)
                                <img
                                    src="{{ $url }}"
                                    alt=""
                                    loading="lazy"
                                    class="max-h-40 max-w-full rounded-md object-cover"
                                />
                            @endforeach
                        </div>
                    @endif

                    @if ($pendingText !== '')
                        <div class="whitespace-pre-wrap rounded-md rounded-br-sm bg-accent px-4 py-3 text-body text-canvas">{{ $pendingText }}</div>
                    @endif
                </div>
            </li>
        @endif

        {{-- Typing indicator: visible while submitPrompt OR runAgent is running --}}
        <li
            wire:loading.flex
            wire:target="submitPrompt,runAgent,photos"
            class="justify-start"
            wire:key="loading"
        >
            <div class="rounded-md bg-surface px-4 py-3 text-caption text-muted">
                <span class="inline-flex gap-1">
                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-muted"></span>
                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-muted [animation-delay:200ms]"></span>
                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-muted [animation-delay:400ms]"></span>
                </span>
            </div>
        </li>
    </ol>

    {{-- Photo preview strip before send --}}
    @if (! empty($photos))
        <div class="flex items-center gap-2 overflow-x-auto border-t border-line bg-surface px-4 py-2">
            @foreach ($photos as $i => $photo)
                <div class="relative shrink-0" wire:key="photo-{{ $i }}-{{ $photo->getFilename() }}">
                    <img
                        src="{{ $photo->temporaryUrl() }}"
                        alt=""
                        class="h-16 w-16 rounded-md object-cover"
                    />
                    <button
                        type="button"
                        wire:click="removePhoto({{ $i }})"
                        class="absolute -right-2 -top-2 flex h-5 w-5 items-center justify-center rounded-full bg-ink text-canvas text-[10px]"
                        aria-label="Remove"
                    >
                        ×
                    </button>
                </div>
            @endforeach
        </div>
    @endif

    <form
        wire:submit="submitPrompt"
        class="flex items-end gap-2 border-t border-line bg-canvas px-4 py-3"
    >
        <label class="flex h-11 w-11 shrink-0 cursor-pointer items-center justify-center rounded-full border-[1.5px] border-line bg-canvas text-text transition hover:border-ink">
            <input
                type="file"
                wire:model="photos"
                accept="image/jpeg,image/png,image/webp"
                multiple
                class="sr-only"
            />
            <span aria-hidden="true">📎</span>
        </label>

        <textarea
            wire:model="draft"
            rows="1"
            placeholder="Just start typing…"
            class="flex-1 resize-none rounded-full border-[1.5px] border-line bg-canvas px-4 py-2.5 text-body text-text placeholder:text-soft-muted focus:border-ink focus:outline-none"
            x-on:keydown.enter.prevent="$el.form.requestSubmit()"
            wire:loading.attr="disabled"
            wire:target="submitPrompt,runAgent"
        ></textarea>

        <button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="submitPrompt,runAgent,photos"
            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-accent text-canvas transition hover:opacity-90 disabled:opacity-50"
            aria-label="Send"
        >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                <path d="M5 12h14M13 5l7 7-7 7"/>
            </svg>
        </button>
    </form>
</div>
