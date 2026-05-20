@props([
    'sectionLabel' => null,
    'buttons' => [],
])

@php
    $channelHref = function (string $channel, string $value): string {
        return match ($channel) {
            'whatsapp' => 'https://wa.me/'.preg_replace('/[^0-9]/', '', $value),
            'line' => 'https://line.me/ti/p/~'.$value,
            'facebook' => str_starts_with($value, 'http') ? $value : 'https://facebook.com/'.$value,
            'call' => 'tel:'.preg_replace('/[^0-9+]/', '', $value),
            default => $value,
        };
    };

    $channelColor = [
        'whatsapp' => 'bg-[#25D366]',
        'line' => 'bg-[#06C755]',
        'facebook' => 'bg-[#1877F2]',
        'call' => 'bg-ink',
    ];

    $channelDefaultLabel = [
        'whatsapp' => 'WhatsApp',
        'line' => 'LINE',
        'facebook' => 'Facebook',
        'call' => 'Call',
    ];
@endphp

<section {{ $attributes->class(['w-full']) }}>
    @if ($sectionLabel)
        <p class="mb-3 text-caption uppercase tracking-wide text-muted">
            {{ $sectionLabel }}
        </p>
    @endif

    <ul class="grid grid-cols-2 gap-3">
        @foreach ($buttons as $button)
            @php
                $channel = $button['channel'] ?? 'call';
                $value = $button['value'] ?? '';
                $label = $button['label'] ?? ($channelDefaultLabel[$channel] ?? ucfirst($channel));
            @endphp
            <li>
                <a
                    href="{{ $channelHref($channel, $value) }}"
                    class="flex items-center gap-3 rounded-md border-[1.5px] border-line bg-canvas px-4 py-3 transition hover:border-ink"
                >
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-sm text-canvas {{ $channelColor[$channel] ?? 'bg-ink' }}">
                        <span aria-hidden="true" class="text-[11px] font-bold">●</span>
                    </span>
                    <span class="text-label text-text">{{ $label }}</span>
                </a>
            </li>
        @endforeach
    </ul>
</section>
