@props([
    'image',
    'title',
    'meta' => null,
    'rating' => null,
    'price',
    'ctaUrl' => '#',
])

<article {{ $attributes->class(['rounded-lg bg-surface p-3']) }}>
    <div class="relative overflow-hidden rounded-md">
        <img
            src="{{ $image }}"
            alt="{{ $title }}"
            loading="lazy"
            class="aspect-[16/10] w-full object-cover"
        />

        <button
            type="button"
            aria-label="Favorisieren"
            class="absolute right-3 top-3 flex h-9 w-9 items-center justify-center rounded-full bg-canvas/90 text-text shadow-sm backdrop-blur-sm transition hover:bg-canvas"
        >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.29 1.51 4.04 3 5.5l7 7Z"/>
            </svg>
        </button>
    </div>

    <div class="px-2 pt-3">
        <h3 class="text-title text-text">{{ $title }}</h3>

        @if ($meta)
            <p class="mt-1 text-caption text-muted">{{ $meta }}</p>
        @endif

        <div class="mt-3 flex items-center justify-between">
            <div class="flex items-baseline gap-2">
                @if ($rating)
                    <span class="inline-flex items-center gap-1 text-label text-text">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-3.5 w-3.5">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                        {{ $rating }}
                    </span>
                    <span class="text-soft-muted">·</span>
                @endif
                <span class="text-label text-text">{{ $price }}</span>
            </div>

            <a
                href="{{ $ctaUrl }}"
                aria-label="Learn more"
                class="flex h-11 w-11 items-center justify-center rounded-full bg-accent text-canvas transition hover:opacity-90"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                    <path d="M5 12h14M13 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>
</article>
