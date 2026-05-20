<div
    x-data="{
        init() {
            window.addEventListener('message', (event) => {
                if (event.data?.type === 'feedai:edit' && typeof event.data.component === 'string') {
                    Livewire.dispatch('open-component-drawer', { type: event.data.component });
                }
            });
        }
    }"
>
    @if ($activeType !== '')
        <div
            class="fixed inset-0 z-50 flex justify-end"
            x-data
            x-on:keydown.escape.window="$wire.close()"
            wire:key="drawer-{{ $activeType }}"
        >
            {{-- Backdrop --}}
            <div
                class="absolute inset-0 bg-black/40"
                wire:click="close"
                aria-hidden="true"
            ></div>

            {{-- Panel --}}
            <aside
                class="relative flex h-full w-full max-w-xl flex-col overflow-hidden bg-canvas shadow-xl"
                role="dialog"
                aria-modal="true"
                aria-labelledby="drawer-title"
            >
                <header class="flex items-center justify-between gap-3 border-b border-line bg-surface px-5 py-3">
                    <div>
                        <p class="text-caption uppercase tracking-wide text-muted">Edit component</p>
                        <h2 id="drawer-title" class="text-title text-text">
                            {{ $schema['label'] ?? \Illuminate\Support\Str::headline($activeType) }}
                        </h2>
                    </div>
                    <button
                        type="button"
                        wire:click="close"
                        class="rounded-full p-2 text-muted transition hover:bg-canvas hover:text-text"
                        aria-label="Close drawer"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-5">
                            <path d="M18 6 6 18M6 6l12 12"/>
                        </svg>
                    </button>
                </header>

                <form wire:submit="save" class="flex min-h-0 flex-1 flex-col">
                    <div class="flex-1 space-y-5 overflow-y-auto px-5 py-5">
                        @if ($error)
                            <div class="rounded-md border border-[#B5564D]/30 bg-[#B5564D]/5 px-4 py-3 text-caption text-[#B5564D]">
                                {{ $error }}
                            </div>
                        @endif

                        @foreach (($schema['fields'] ?? []) as $key => $def)
                            @php($fieldType = $def['type'] ?? 'string')
                            @php($required = (bool) ($def['required'] ?? false))
                            @php($label = \Illuminate\Support\Str::headline($key))
                            @php($description = $def['description'] ?? null)

                            <div class="space-y-1.5">
                                <label class="flex items-baseline gap-2">
                                    <span class="text-label text-text">{{ $label }}</span>
                                    @if ($required)
                                        <span class="text-caption text-[#B5564D]">required</span>
                                    @endif
                                </label>
                                @if ($description)
                                    <p class="text-caption text-muted">{{ $description }}</p>
                                @endif

                                @switch($fieldType)
                                    @case('image')
                                        @php($currentUrl = $fields[$key] ?? '')
                                        <div class="space-y-2">
                                            @if ($currentUrl)
                                                <img src="{{ $currentUrl }}" alt="" class="max-h-48 rounded-md border border-line object-cover" />
                                            @endif
                                            @if ($uploads[$key] ?? null)
                                                <img src="{{ $uploads[$key]->temporaryUrl() }}" alt="" class="max-h-48 rounded-md border border-line object-cover" />
                                            @endif
                                            <input
                                                type="file"
                                                wire:model="uploads.{{ $key }}"
                                                accept="image/jpeg,image/png,image/webp"
                                                class="block w-full rounded-md border border-line bg-surface px-3 py-2 text-caption text-text"
                                            />
                                            @error("uploads.{$key}")
                                                <p class="text-caption text-[#B5564D]">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    @break

                                    @case('enum')
                                        @php($choices = $def['choices'] ?? [])
                                        <select
                                            wire:model="fields.{{ $key }}"
                                            class="w-full rounded-md border border-line bg-canvas px-3 py-2 text-body text-text"
                                        >
                                            @foreach ($choices as $choice)
                                                <option value="{{ $choice }}">{{ $choice }}</option>
                                            @endforeach
                                        </select>
                                    @break

                                    @case('array')
                                        @php($items = is_array($fields[$key] ?? null) ? $fields[$key] : [])
                                        <div class="space-y-3">
                                            @foreach ($items as $i => $item)
                                                <div class="rounded-md border border-line bg-surface p-3" wire:key="{{ $activeType }}-{{ $key }}-{{ $i }}">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <span class="text-caption uppercase tracking-wide text-muted">#{{ $i + 1 }}</span>
                                                        <button
                                                            type="button"
                                                            wire:click="removeItem('{{ $key }}', {{ $i }})"
                                                            class="text-caption text-[#B5564D] underline"
                                                        >
                                                            Remove
                                                        </button>
                                                    </div>
                                                    @include('livewire.feed._item-fields', ['type' => $activeType, 'field' => $key, 'index' => $i])
                                                </div>
                                            @endforeach
                                            <button
                                                type="button"
                                                wire:click="addItem('{{ $key }}')"
                                                class="w-full rounded-md border-2 border-dashed border-line bg-surface px-3 py-2 text-label text-text transition hover:border-ink"
                                            >
                                                ⊕ Add item
                                            </button>
                                        </div>
                                    @break

                                    @default
                                        @if (($def['max'] ?? 0) > 120 || $key === 'body')
                                            <textarea
                                                wire:model.live.debounce.500ms="fields.{{ $key }}"
                                                rows="4"
                                                class="w-full rounded-md border border-line bg-canvas px-3 py-2 text-body text-text"
                                            ></textarea>
                                        @else
                                            <input
                                                type="text"
                                                wire:model.live.debounce.500ms="fields.{{ $key }}"
                                                class="w-full rounded-md border border-line bg-canvas px-3 py-2 text-body text-text"
                                            />
                                        @endif
                                @endswitch
                            </div>
                        @endforeach

                        {{-- About-style markdown body — schemas without an explicit
                             body field still support it via the loader, so we expose
                             a textarea when the type traditionally uses one. --}}
                        @if (in_array($activeType, ['about', 'text_block', 'image_with_text'], true))
                            <div class="space-y-1.5">
                                <label class="text-label text-text">Body (Markdown)</label>
                                <textarea
                                    wire:model.live.debounce.500ms="body"
                                    rows="6"
                                    class="w-full rounded-md border border-line bg-canvas px-3 py-2 text-body text-text"
                                ></textarea>
                            </div>
                        @endif
                    </div>

                    <footer class="flex items-center justify-end gap-3 border-t border-line bg-surface px-5 py-3">
                        <button
                            type="button"
                            wire:click="close"
                            class="rounded-md px-4 py-2 text-label text-muted transition hover:text-text"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="save,uploads,itemUploads"
                            class="rounded-md bg-accent px-4 py-2 text-label text-canvas transition hover:opacity-90 disabled:opacity-50"
                        >
                            <span wire:loading.remove wire:target="save,uploads,itemUploads">Save</span>
                            <span wire:loading wire:target="save,uploads,itemUploads">Saving…</span>
                        </button>
                    </footer>
                </form>
            </aside>
        </div>
    @endif
</div>
