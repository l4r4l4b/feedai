@props([
    'title',
    'body' => null,
    'buttonLabel',
    'buttonUrl',
])

<section {{ $attributes->class(['rounded-lg bg-surface p-6 text-center']) }}>
    <h2 class="text-section text-text">{{ $title }}</h2>
    @if ($body)
        <p class="mx-auto mt-2 max-w-md text-caption text-muted">{{ $body }}</p>
    @endif
    <a
        href="{{ $buttonUrl }}"
        class="mt-5 inline-flex items-center justify-center rounded-full bg-accent px-6 py-3 text-label text-canvas transition hover:opacity-90"
    >
        {{ $buttonLabel }}
    </a>
</section>
