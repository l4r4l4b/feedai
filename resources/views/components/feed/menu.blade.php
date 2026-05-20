@props([
    'sectionLabel' => null,
    'items' => [],
])

<section {{ $attributes->class(['w-full']) }}>
    @if ($sectionLabel)
        <p class="mb-4 font-mono text-mono-label uppercase text-muted">
            {{ $sectionLabel }}
        </p>
    @endif

    <ul class="grid grid-cols-2 gap-4">
        @foreach ($items as $item)
            <li class="flex flex-col">
                @if (! empty($item['image']))
                    <div class="overflow-hidden rounded-image">
                        <img
                            src="{{ $item['image'] }}"
                            alt="{{ $item['name'] ?? '' }}"
                            loading="lazy"
                            class="aspect-square w-full object-cover"
                        />
                    </div>
                @endif

                <div class="mt-2 flex items-baseline justify-between gap-2">
                    <span class="text-body text-ink">{{ $item['name'] ?? '' }}</span>
                    @if (! empty($item['price']))
                        <span class="font-mono text-mono-label uppercase text-muted">{{ $item['price'] }}</span>
                    @endif
                </div>

                @if (! empty($item['description']))
                    <p class="mt-1 text-caption text-muted">{{ $item['description'] }}</p>
                @endif
            </li>
        @endforeach
    </ul>
</section>
