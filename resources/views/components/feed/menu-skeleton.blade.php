@props(['type'])

<div
    class="group relative flex w-full cursor-pointer flex-col gap-4 rounded-lg border-2 border-dashed border-line bg-surface p-6 transition hover:border-ink"
    role="button"
    tabindex="0"
    onclick="window.parent?.postMessage({type:'feedai:edit', component:'{{ $type }}'}, '*')"
    onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();window.parent?.postMessage({type:'feedai:edit', component:'{{ $type }}'}, '*')}"
>
    <div class="flex items-center justify-between gap-3">
        <p class="text-caption uppercase tracking-wide text-muted">Menu · empty</p>
        <span class="text-caption text-muted opacity-0 transition group-hover:opacity-100">⊕ Add content</span>
    </div>

    <ul class="space-y-3">
        @for ($i = 0; $i < 3; $i++)
            <li class="flex items-center gap-3">
                <div class="size-14 shrink-0 rounded-md bg-soft-muted/30"></div>
                <div class="flex-1 space-y-1.5">
                    <div class="h-3 w-2/3 rounded bg-soft-muted/30"></div>
                    <div class="h-2.5 w-1/3 rounded bg-soft-muted/20"></div>
                </div>
                <div class="h-3 w-12 rounded bg-soft-muted/30"></div>
            </li>
        @endfor
    </ul>

    <p class="mt-1 text-caption text-soft-muted">Add 3–5 items — dishes, tours or services with names and prices.</p>
</div>
