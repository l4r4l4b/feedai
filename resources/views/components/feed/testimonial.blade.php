@props([
    'quote',
    'author',
    'role' => null,
])

<figure {{ $attributes->class(['rounded-card border border-line bg-soft p-6']) }}>
    <blockquote class="text-section font-medium leading-snug text-ink">
        „{{ $quote }}"
    </blockquote>
    <figcaption class="mt-4 flex items-baseline gap-2 text-caption">
        <span class="text-ink">{{ $author }}</span>
        @if ($role)
            <span class="text-muted">— {{ $role }}</span>
        @endif
    </figcaption>
</figure>
