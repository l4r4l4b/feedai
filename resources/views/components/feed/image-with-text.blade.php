@props([
    'image',
    'headline' => null,
    'body' => '',
])

<section {{ $attributes->class(['w-full']) }}>
    <div class="overflow-hidden rounded-md">
        <img
            src="{{ $image }}"
            alt="{{ $headline ?? '' }}"
            loading="lazy"
            class="aspect-video w-full object-cover"
        />
    </div>

    @if ($headline)
        <h2 class="mt-4 text-section text-text">{{ $headline }}</h2>
    @endif

    <div class="mt-3 prose prose-neutral max-w-[70ch] text-body text-text prose-p:my-2 prose-strong:text-text">
        {!! \Illuminate\Support\Str::markdown($body) !!}
    </div>
</section>
