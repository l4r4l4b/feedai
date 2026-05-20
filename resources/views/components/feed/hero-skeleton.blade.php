@props(['type'])

<div
    class="group relative flex aspect-[3/4] w-full cursor-pointer items-center justify-center overflow-hidden rounded-lg border-2 border-dashed border-line bg-surface text-center transition hover:border-ink"
    role="button"
    tabindex="0"
    onclick="window.parent?.postMessage({type:'feedai:edit', component:'{{ $type }}'}, '*')"
    onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();window.parent?.postMessage({type:'feedai:edit', component:'{{ $type }}'}, '*')}"
>
    <div class="flex flex-col items-center gap-3 px-6 text-muted">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-10 opacity-60">
            <rect x="3" y="3" width="18" height="18" rx="2"/>
            <circle cx="9" cy="9" r="2"/>
            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
        </svg>
        <p class="text-caption uppercase tracking-wide">Hero · empty</p>
        <p class="text-body text-soft-muted">Add a photo and headline for the top of your feed.</p>
        <span class="mt-2 inline-flex items-center gap-1 rounded-full bg-ink px-4 py-2 text-label text-canvas group-hover:opacity-90">
            ⊕ Add content
        </span>
    </div>
</div>
