@props([
    'image',
    'title',
    'meta' => null,
    'price' => null,
    'description' => null,
    'ctaLabel' => 'Buchen',
    'ctaUrl' => '#',
])

<article {{ $attributes->class(['rounded-card border border-line bg-soft p-5']) }}>
    <div class="overflow-hidden rounded-image">
        <img
            src="{{ $image }}"
            alt="{{ $title }}"
            loading="lazy"
            class="aspect-[16/9] w-full object-cover"
        />
    </div>

    <h2 class="mt-4 text-section text-ink">{{ $title }}</h2>

    @if ($description)
        <p class="mt-2 text-caption text-muted">{{ $description }}</p>
    @endif

    @if ($meta || $price)
        <div class="mt-3 flex items-center justify-between gap-3 text-caption">
            <span class="text-muted">{{ $meta }}</span>
            @if ($price)
                <span class="font-mono text-mono-label uppercase text-ink">{{ $price }}</span>
            @endif
        </div>
    @endif

    <a
        href="{{ $ctaUrl }}"
        class="mt-4 inline-flex w-full items-center justify-center rounded-button bg-accent px-4 py-3 text-caption font-medium text-card transition hover:opacity-90"
    >
        {{ $ctaLabel }}
    </a>
</article>
