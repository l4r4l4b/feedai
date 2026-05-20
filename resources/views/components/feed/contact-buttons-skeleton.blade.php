@props(['type'])

<div
    class="group relative flex w-full cursor-pointer flex-col gap-4 rounded-lg border-2 border-dashed border-line bg-surface p-6 transition hover:border-ink"
    role="button"
    tabindex="0"
    onclick="window.parent?.postMessage({type:'feedai:edit', component:'{{ $type }}'}, '*')"
    onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();window.parent?.postMessage({type:'feedai:edit', component:'{{ $type }}'}, '*')}"
>
    <div class="flex items-center justify-between gap-3">
        <p class="text-caption uppercase tracking-wide text-muted">Contact · empty</p>
        <span class="text-caption text-muted opacity-0 transition group-hover:opacity-100">⊕ Add content</span>
    </div>

    <ul class="grid grid-cols-2 gap-3">
        @for ($i = 0; $i < 2; $i++)
            <li class="flex items-center gap-3 rounded-md border border-line/60 bg-canvas px-4 py-3">
                <div class="size-6 shrink-0 rounded bg-soft-muted/30"></div>
                <div class="h-3 flex-1 rounded bg-soft-muted/30"></div>
            </li>
        @endfor
    </ul>

    <p class="mt-1 text-caption text-soft-muted">Pick at least one channel: WhatsApp, LINE, phone or Facebook.</p>
</div>
