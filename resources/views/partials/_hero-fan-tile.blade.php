{{-- Thumbnail-sized variant of <x-feed.hero>. Same composition (image + location
     + title overlay) but with smaller fonts so it fits in the hero fan phones
     without wrapping. `$emphasised = true` bumps the title size for the centre
     phone. --}}
@php
    $emphasised = $emphasised ?? false;
    $titleClass = $emphasised
        ? 'text-[18px] sm:text-[20px] font-bold leading-tight'
        : 'text-[15px] sm:text-[16px] font-bold leading-tight';
    $locClass = $emphasised
        ? 'text-[10px] sm:text-[11px]'
        : 'text-[9px] sm:text-[10px]';
@endphp

<div class="relative aspect-[3/4] w-full overflow-hidden rounded-[22px] bg-surface">
    <img
        src="{{ $image }}"
        alt="{{ $name }}"
        loading="lazy"
        class="h-full w-full object-cover"
    />

    {{-- Favourite heart, decorative only (matches x-feed.hero ornament) --}}
    <span aria-hidden="true" class="absolute right-2.5 top-2.5 flex h-6 w-6 items-center justify-center rounded-full bg-canvas/90 text-text shadow-sm backdrop-blur-sm">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-3 w-3">
            <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.29 1.51 4.04 3 5.5l7 7Z"/>
        </svg>
    </span>

    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/85 via-black/35 to-transparent px-3 pb-3 pt-10 text-canvas">
        @if (! empty($location))
            <p class="flex items-center gap-1 {{ $locClass }} uppercase tracking-wide opacity-85">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-2.5 w-2.5">
                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                    <circle cx="12" cy="10" r="3"/>
                </svg>
                <span class="truncate">{{ $location }}</span>
            </p>
        @endif

        <h3 class="mt-0.5 {{ $titleClass }}">{{ $name }}</h3>
    </div>
</div>
