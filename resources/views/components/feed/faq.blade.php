@props([
    'sectionLabel' => null,
    'items' => [],
])

<section {{ $attributes->class(['w-full']) }}>
    @if ($sectionLabel)
        <p class="mb-3 font-mono text-mono-label uppercase text-muted">
            {{ $sectionLabel }}
        </p>
    @endif

    <div class="divide-y divide-line rounded-card border border-line bg-card">
        @foreach ($items as $item)
            <details class="group px-4 py-3">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-body text-ink">
                    <span>{{ $item['question'] ?? '' }}</span>
                    <span class="font-mono text-mono-label text-muted group-open:rotate-45 transition">+</span>
                </summary>
                <p class="mt-2 text-caption text-muted">{{ $item['answer'] ?? '' }}</p>
            </details>
        @endforeach
    </div>
</section>
