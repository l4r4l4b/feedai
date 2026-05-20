@props([
    'sectionLabel' => null,
    'address',
    'mapUrl' => null,
    'embedUrl' => null,
])

<section {{ $attributes->class(['w-full']) }}>
    @if ($sectionLabel)
        <p class="mb-3 font-mono text-mono-label uppercase text-muted">
            {{ $sectionLabel }}
        </p>
    @endif

    @if ($embedUrl)
        <div class="overflow-hidden rounded-image border border-line">
            <iframe
                src="{{ $embedUrl }}"
                loading="lazy"
                class="aspect-[4/3] w-full"
                referrerpolicy="no-referrer-when-downgrade"
            ></iframe>
        </div>
    @endif

    <div class="mt-3 rounded-card border border-line bg-card px-4 py-3">
        <p class="text-body text-ink">{{ $address }}</p>
        @if ($mapUrl)
            <a
                href="{{ $mapUrl }}"
                target="_blank"
                rel="noreferrer"
                class="mt-2 inline-flex font-mono text-mono-label uppercase text-warm"
            >
                In Maps öffnen ↗
            </a>
        @endif
    </div>
</section>
