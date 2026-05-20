@props([
    'label' => 'BEZAHLEN',
    'title',
    'url' => '#pay',
])

<a
    href="{{ $url }}"
    {{ $attributes->class(['flex items-center justify-between gap-4 rounded-card bg-accent px-6 py-5 text-card transition hover:opacity-90']) }}
>
    <div class="flex flex-col">
        <span class="font-mono text-mono-label uppercase opacity-70">{{ $label }}</span>
        <span class="mt-1 text-section font-medium">{{ $title }}</span>
    </div>

    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/10 text-section">
        →
    </span>
</a>
