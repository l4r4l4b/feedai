@props([
    'image',
    'title',
    'location' => null,
    'rating' => null,
])

<section {{ $attributes->class(['w-full']) }}>
    <div class="relative aspect-[3/4] w-full overflow-hidden rounded-xl bg-surface">
        <img
            src="{{ $image }}"
            alt="{{ $title }}"
            loading="lazy"
            class="h-full w-full object-cover"
        />

        {{-- Favoriten-Herz oben rechts, UI-Ornament, kein State --}}
        <button
            type="button"
            aria-label="Favorisieren"
            class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-canvas/90 text-text shadow-sm backdrop-blur-sm transition hover:bg-canvas"
        >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.29 1.51 4.04 3 5.5l7 7Z"/>
            </svg>
        </button>

        {{-- Bottom-Overlay mit Gradient + Text --}}
        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/85 via-black/40 to-transparent px-6 pb-6 pt-16 text-canvas">
            @if ($location)
                <p class="flex items-center gap-1.5 text-caption opacity-90">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5">
                        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                    {{ $location }}
                </p>
            @endif

            <h1 class="mt-1 text-display md:text-display-lg">
                {{ $title }}
            </h1>

            @if ($rating)
                <span class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-canvas/90 px-3 py-1 text-caption text-text backdrop-blur-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-3.5 w-3.5">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    {{ $rating }}
                </span>
            @endif
        </div>
    </div>
</section>
