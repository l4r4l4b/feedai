@props([
    'icon',
    'headline',
    'body' => null,
])

<aside {{ $attributes->class(['flex items-start gap-4 rounded-card border border-line bg-card px-5 py-4']) }}>
    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-[10px] bg-soft text-section">
        {{ $icon }}
    </span>

    <div>
        <p class="text-body font-medium text-ink">{{ $headline }}</p>
        @if ($body)
            <p class="mt-1 text-caption text-muted">{{ $body }}</p>
        @endif
    </div>
</aside>
