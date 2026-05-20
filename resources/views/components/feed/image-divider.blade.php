@props([
    'image',
    'headline' => null,
    'sub' => null,
])

<section {{ $attributes->class(['relative overflow-hidden rounded-lg']) }}>
    <img
        src="{{ $image }}"
        alt="{{ $headline ?? '' }}"
        loading="lazy"
        class="aspect-video w-full object-cover"
    />

    @if ($headline || $sub)
        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/75 via-black/35 to-transparent px-6 pb-5 pt-12 text-canvas">
            @if ($headline)
                <p class="text-section">{{ $headline }}</p>
            @endif
            @if ($sub)
                <p class="mt-1 text-caption opacity-85">{{ $sub }}</p>
            @endif
        </div>
    @endif
</section>
