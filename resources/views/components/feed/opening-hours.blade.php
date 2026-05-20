@props([
    'sectionLabel' => null,
    'hours' => [],
    'note' => null,
])

<section {{ $attributes->class(['w-full']) }}>
    @if ($sectionLabel)
        <p class="mb-3 font-mono text-mono-label uppercase text-muted">
            {{ $sectionLabel }}
        </p>
    @endif

    <dl class="divide-y divide-line rounded-card border border-line bg-card">
        @foreach ($hours as $row)
            <div class="flex items-center justify-between px-4 py-3">
                <dt class="text-body text-ink">{{ $row['days'] ?? '' }}</dt>
                <dd class="font-mono text-mono-label uppercase text-muted">{{ $row['time'] ?? '' }}</dd>
            </div>
        @endforeach
    </dl>

    @if ($note)
        <p class="mt-2 text-caption text-muted">{{ $note }}</p>
    @endif
</section>
