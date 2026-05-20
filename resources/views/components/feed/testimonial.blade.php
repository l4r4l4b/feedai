@props([
    'quote',
    'author',
    'role' => null,
])

<figure {{ $attributes->class(['rounded-lg bg-surface p-6']) }}>
    <blockquote class="text-section text-text">
        „{{ $quote }}"
    </blockquote>
    <figcaption class="mt-4 flex items-baseline gap-2 text-caption">
        <span class="font-bold text-text">{{ $author }}</span>
        @if ($role)
            <span class="text-muted">— {{ $role }}</span>
        @endif
    </figcaption>
</figure>
