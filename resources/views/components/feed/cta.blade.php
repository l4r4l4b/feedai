@props([
    'title',
    'body' => null,
    'buttonLabel',
    'buttonUrl',
])

<section {{ $attributes->class(['rounded-card border border-line bg-soft p-6 text-center']) }}>
    <h2 class="text-section text-ink">{{ $title }}</h2>
    @if ($body)
        <p class="mx-auto mt-2 max-w-md text-caption text-muted">{{ $body }}</p>
    @endif
    <a
        href="{{ $buttonUrl }}"
        class="mt-5 inline-flex items-center justify-center rounded-button bg-accent px-5 py-3 text-caption font-medium text-card transition hover:opacity-90"
    >
        {{ $buttonLabel }}
    </a>
</section>
