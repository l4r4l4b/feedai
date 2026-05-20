@props([
    'label' => 'BEZAHLEN',
    'title',
    'url' => '#pay',
])

<a
    href="{{ $url }}"
    {{ $attributes->class(['flex items-center justify-between gap-4 rounded-lg bg-accent px-6 py-5 text-canvas transition hover:opacity-90']) }}
>
    <div class="flex flex-col">
        <span class="text-caption uppercase tracking-wide opacity-70">{{ $label }}</span>
        <span class="mt-1 text-section">{{ $title }}</span>
    </div>

    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white/14 transition group-hover:bg-white/20">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
            <path d="M5 12h14M13 5l7 7-7 7"/>
        </svg>
    </span>
</a>
