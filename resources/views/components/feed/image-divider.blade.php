@props([
    'image',
    'headline' => null,
    'sub' => null,
])

<section {{ $attributes->class(['relative -mx-5 overflow-hidden md:-mx-8']) }}>
    <img
        src="{{ $image }}"
        alt="{{ $headline ?? '' }}"
        loading="lazy"
        class="aspect-[21/9] w-full object-cover"
    />

    @if ($headline || $sub)
        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 via-black/30 to-transparent px-6 pb-5 pt-12 text-card">
            @if ($headline)
                <p class="text-section font-medium">{{ $headline }}</p>
            @endif
            @if ($sub)
                <p class="mt-1 text-caption opacity-85">{{ $sub }}</p>
            @endif
        </div>
    @endif
</section>
