@props([
    'sectionLabel' => null,
    'address',
    'mapUrl' => null,
    'embedUrl' => null,
])

<section {{ $attributes->class(['w-full']) }}>
    @if ($sectionLabel)
        <p class="mb-3 text-caption uppercase tracking-wide text-muted">
            {{ $sectionLabel }}
        </p>
    @endif

    @if ($embedUrl)
        <div class="overflow-hidden rounded-md border border-line">
            <iframe
                src="{{ $embedUrl }}"
                loading="lazy"
                class="aspect-[4/3] w-full"
                referrerpolicy="no-referrer-when-downgrade"
            ></iframe>
        </div>
    @endif

    <div class="mt-3 rounded-md border border-line bg-canvas px-4 py-3">
        <p class="text-body text-text">{{ $address }}</p>
        @if ($mapUrl)
            <a
                href="{{ $mapUrl }}"
                target="_blank"
                rel="noreferrer"
                class="mt-2 inline-flex text-label text-text underline"
            >
                Open in Maps ↗
            </a>
        @endif
    </div>
</section>
