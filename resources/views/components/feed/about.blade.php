@props([
    'sectionLabel' => null,
    'body' => '',
])

<section {{ $attributes->class(['w-full']) }}>
    @if ($sectionLabel)
        <p class="mb-3 font-mono text-mono-label uppercase text-muted">
            {{ $sectionLabel }}
        </p>
    @endif

    <div class="prose prose-neutral max-w-[70ch] text-body text-ink prose-p:my-3 prose-strong:text-ink prose-a:text-warm">
        {!! \Illuminate\Support\Str::markdown($body) !!}
    </div>
</section>
