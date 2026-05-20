@props(['type'])

<div
    class="group relative flex w-full cursor-pointer flex-col gap-3 rounded-lg border-2 border-dashed border-line bg-surface p-6 transition hover:border-ink"
    role="button"
    tabindex="0"
    onclick="window.parent?.postMessage({type:'feedai:edit', component:'{{ $type }}'}, '*')"
    onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();window.parent?.postMessage({type:'feedai:edit', component:'{{ $type }}'}, '*')}"
>
    <div class="flex items-center justify-between gap-3">
        <p class="text-caption uppercase tracking-wide text-muted">About · empty</p>
        <span class="text-caption text-muted opacity-0 transition group-hover:opacity-100">⊕ Add content</span>
    </div>
    <div class="space-y-2">
        <div class="h-3 w-3/4 rounded bg-soft-muted/30"></div>
        <div class="h-3 w-full rounded bg-soft-muted/30"></div>
        <div class="h-3 w-5/6 rounded bg-soft-muted/30"></div>
        <div class="h-3 w-2/3 rounded bg-soft-muted/30"></div>
    </div>
    <p class="mt-1 text-caption text-soft-muted">Tell guests who you are in 2–4 sentences.</p>
</div>
