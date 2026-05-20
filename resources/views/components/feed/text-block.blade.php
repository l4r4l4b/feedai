@props([
    'headline' => null,
    'body' => '',
])

<section {{ $attributes->class(['w-full py-2']) }}>
    @if ($headline)
        <h2 class="mb-3 text-section text-ink">{{ $headline }}</h2>
    @endif

    <div class="prose prose-neutral max-w-[70ch] text-body text-ink prose-p:my-3 prose-strong:text-ink">
        {!! \Illuminate\Support\Str::markdown($body) !!}
    </div>
</section>
