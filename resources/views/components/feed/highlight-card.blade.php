@props([
    'icon',
    'headline',
    'body' => null,
])

<aside {{ $attributes->class(['flex items-start gap-4 rounded-lg bg-surface px-5 py-4']) }}>
    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-sm bg-ink text-canvas">
        {{ $icon }}
    </span>

    <div>
        <p class="text-label text-text">{{ $headline }}</p>
        @if ($body)
            <p class="mt-1 text-caption text-muted">{{ $body }}</p>
        @endif
    </div>
</aside>
