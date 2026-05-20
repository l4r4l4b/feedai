@props([
    'sectionLabel' => null,
    'ratio' => '1:1',
    'images' => [],
])

@php
    $aspectClass = $ratio === '4:5' ? 'aspect-[4/5]' : 'aspect-square';
@endphp

<section {{ $attributes->class(['w-full']) }}>
    @if ($sectionLabel)
        <p class="mb-3 font-mono text-mono-label uppercase text-muted">
            {{ $sectionLabel }}
        </p>
    @endif

    <ul class="grid grid-cols-2 gap-2 md:grid-cols-3">
        @foreach ($images as $image)
            <li class="overflow-hidden rounded-image">
                <img
                    src="{{ $image['src'] ?? '' }}"
                    alt="{{ $image['alt'] ?? '' }}"
                    loading="lazy"
                    class="{{ $aspectClass }} w-full object-cover"
                />
            </li>
        @endforeach
    </ul>
</section>
