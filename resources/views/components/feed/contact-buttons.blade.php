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
        'whatsapp' => 'text-[#25D366]',
        'line' => 'text-[#06C755]',
        'facebook' => 'text-[#1877F2]',
        'call' => 'text-ink',
    ];

    $channelDefaultLabel = [
        'whatsapp' => 'WhatsApp',
        'line' => 'LINE',
        'facebook' => 'Facebook',
        'call' => 'Anrufen',
    ];
@endphp

<section {{ $attributes->class(['w-full']) }}>
    @if ($sectionLabel)
        <p class="mb-3 font-mono text-mono-label uppercase text-muted">
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
                    class="flex items-center gap-3 rounded-card border border-line bg-card px-4 py-3 transition hover:bg-soft"
                >
                    <span class="{{ $channelColor[$channel] ?? 'text-ink' }} text-section">●</span>
                    <span class="text-body text-ink">{{ $label }}</span>
                </a>
            </li>
        @endforeach
    </ul>
</section>
