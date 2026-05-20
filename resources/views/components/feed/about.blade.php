@props([
    'sectionLabel' => null,
    'body' => '',
])

<section {{ $attributes->class(['w-full']) }}>
    @if ($sectionLabel)
        <p class="mb-3 text-caption uppercase tracking-wide text-muted">
            {{ $sectionLabel }}
        </p>
    @endif

    <div class="prose prose-neutral max-w-[70ch] text-body text-text prose-p:my-3 prose-strong:text-text prose-a:font-bold prose-a:text-text prose-a:underline">
        {!! \Illuminate\Support\Str::markdown($body) !!}
    </div>
</section>
