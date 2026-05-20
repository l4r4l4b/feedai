@props([
    'image',
    'title',
    'subtitle' => null,
    'badgeLabel' => null,
])

<section {{ $attributes->class(['w-full']) }}>
    <div class="relative aspect-[4/5] w-full overflow-hidden rounded-image bg-soft">
        <img
            src="{{ $image }}"
            alt="{{ $title }}"
            loading="lazy"
            class="h-full w-full object-cover"
        />

        @if ($badgeLabel)
            <span class="absolute left-4 top-4 inline-flex items-center rounded-full bg-card/85 px-3 py-1 font-mono text-mono-label uppercase text-ink backdrop-blur-sm">
                {{ $badgeLabel }}
            </span>
        @endif
    </div>

    <div class="mt-5 px-1">
        <h1 class="text-hero text-ink md:text-hero-lg">
            {{ $title }}
        </h1>

        @if ($subtitle)
            <p class="mt-2 text-body text-muted">
                {{ $subtitle }}
            </p>
        @endif
    </div>
</section>
