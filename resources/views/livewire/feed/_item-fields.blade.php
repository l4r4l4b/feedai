{{-- Per-item subfields rendered inside an array repeater. Hardcoded per
     component type since the schema YAML expresses item_fields as plain
     strings; a future schema refactor could replace this with a generic loop. --}}

@php($prefix = "fields.{$field}.{$index}")

@switch("{$type}.{$field}")
    @case('menu.items')
        <div class="grid gap-2 md:grid-cols-2">
            <input
                type="text"
                wire:model.live.debounce.500ms="{{ $prefix }}.name"
                placeholder="Name"
                class="rounded-md border border-line bg-canvas px-3 py-2 text-body text-text"
            />
            <input
                type="text"
                wire:model.live.debounce.500ms="{{ $prefix }}.price"
                placeholder="Price (e.g. 80 THB)"
                class="rounded-md border border-line bg-canvas px-3 py-2 text-body text-text"
            />
        </div>
        <textarea
            wire:model.live.debounce.500ms="{{ $prefix }}.description"
            rows="2"
            placeholder="Short description (max 12 words)"
            class="mt-2 w-full rounded-md border border-line bg-canvas px-3 py-2 text-body text-text"
        ></textarea>
        <div class="mt-2 space-y-2">
            @if (! empty($fields[$field][$index]['image']))
                <img src="{{ $fields[$field][$index]['image'] }}" alt="" class="max-h-32 rounded-md object-cover" />
            @endif
            @if ($itemUploads["{$field}.{$index}"] ?? null)
                <img src="{{ $itemUploads["{$field}.{$index}"]->temporaryUrl() }}" alt="" class="max-h-32 rounded-md object-cover" />
            @endif
            <input
                type="file"
                wire:model="itemUploads.{{ $field }}.{{ $index }}"
                accept="image/jpeg,image/png,image/webp"
                class="block w-full rounded-md border border-line bg-canvas px-3 py-2 text-caption text-text"
            />
        </div>
    @break

    @case('opening_hours.hours')
        <div class="grid gap-2 md:grid-cols-2">
            <input
                type="text"
                wire:model.live.debounce.500ms="{{ $prefix }}.days"
                placeholder="Mon–Fri"
                class="rounded-md border border-line bg-canvas px-3 py-2 text-body text-text"
            />
            <input
                type="text"
                wire:model.live.debounce.500ms="{{ $prefix }}.time"
                placeholder="17:00–23:00"
                class="rounded-md border border-line bg-canvas px-3 py-2 text-body text-text"
            />
        </div>
    @break

    @case('contact_buttons.buttons')
        <div class="grid gap-2 md:grid-cols-3">
            <select
                wire:model.live="{{ $prefix }}.channel"
                class="rounded-md border border-line bg-canvas px-3 py-2 text-body text-text"
            >
                <option value="whatsapp">WhatsApp</option>
                <option value="line">LINE</option>
                <option value="call">Phone</option>
                <option value="facebook">Facebook</option>
            </select>
            <input
                type="text"
                wire:model.live.debounce.500ms="{{ $prefix }}.value"
                placeholder="Number / URL / handle"
                class="rounded-md border border-line bg-canvas px-3 py-2 text-body text-text md:col-span-2"
            />
        </div>
        <input
            type="text"
            wire:model.live.debounce.500ms="{{ $prefix }}.label"
            placeholder="Button label (optional)"
            class="mt-2 w-full rounded-md border border-line bg-canvas px-3 py-2 text-body text-text"
        />
    @break

    @default
        <p class="text-caption text-muted">No editor for this item type yet.</p>
@endswitch
