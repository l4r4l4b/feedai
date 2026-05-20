@props(['type'])

{{-- Builder-only overlay on top of a filled component. Two actions:
     ✎ Edit with form — postMessages parent to open ComponentDrawer
     💬 Edit in chat — postMessages parent to prefill the chat textarea --}}
<div
    class="absolute right-2 top-2 z-10 flex items-center gap-1 rounded-full bg-ink/85 px-1 py-1 shadow-md backdrop-blur-sm"
    role="toolbar"
    aria-label="{{ __('Edit options') }}"
>
    <button
        type="button"
        onclick="window.parent?.postMessage({type:'feedai:edit', component:'{{ $type }}'}, '*')"
        class="flex h-8 w-8 items-center justify-center rounded-full text-canvas transition hover:bg-canvas/10"
        title="{{ __('Edit with form') }}"
        aria-label="{{ __('Edit with form') }}"
    >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
        </svg>
    </button>
    <button
        type="button"
        onclick="window.parent?.postMessage({type:'feedai:edit-in-chat', component:'{{ $type }}'}, '*')"
        class="flex h-8 w-8 items-center justify-center rounded-full text-canvas transition hover:bg-canvas/10"
        title="{{ __('Discuss in chat') }}"
        aria-label="{{ __('Discuss in chat') }}"
    >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
        </svg>
    </button>
</div>
