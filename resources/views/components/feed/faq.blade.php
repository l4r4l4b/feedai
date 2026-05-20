@props([
    'sectionLabel' => null,
    'items' => [],
])

<section {{ $attributes->class(['w-full']) }}>
    @if ($sectionLabel)
        <p class="mb-3 text-caption uppercase tracking-wide text-muted">
            {{ $sectionLabel }}
        </p>
    @endif

    <div class="divide-y divide-line rounded-md border border-line bg-canvas">
        @foreach ($items as $item)
            <details class="group px-4 py-3">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-label text-text">
                    <span>{{ $item['question'] ?? '' }}</span>
                    <span class="text-muted transition group-open:rotate-45">+</span>
                </summary>
                <p class="mt-2 text-caption text-muted">{{ $item['answer'] ?? '' }}</p>
            </details>
        @endforeach
    </div>
</section>
