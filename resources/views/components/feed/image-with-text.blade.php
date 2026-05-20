@props([
    'image',
    'headline' => null,
    'body' => '',
])

<section {{ $attributes->class(['w-full']) }}>
    <div class="overflow-hidden rounded-image">
        <img
            src="{{ $image }}"
            alt="{{ $headline ?? '' }}"
            loading="lazy"
            class="aspect-[16/9] w-full object-cover"
        />
    </div>

    @if ($headline)
        <h2 class="mt-4 text-section text-ink">{{ $headline }}</h2>
    @endif

    <div class="mt-3 prose prose-neutral max-w-[70ch] text-body text-ink prose-p:my-2 prose-strong:text-ink">
        {!! \Illuminate\Support\Str::markdown($body) !!}
    </div>
</section>
