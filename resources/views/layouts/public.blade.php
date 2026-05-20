<!DOCTYPE html>
<html lang="{{ $vendor['locale'] ?? 'th' }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex" />
    <title>{{ $vendor['name'] ?? 'FeedAI' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anuphan:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])
</head>
<body
    class="bg-canvas text-text antialiased"
    @if (! empty($vendor['accent_color'])) style="--accent: {{ $vendor['accent_color'] }};" @endif
>
    <a id="top" class="sr-only" tabindex="-1">{{ __('Top') }}</a>

    <main class="mx-auto min-h-screen w-full max-w-md px-5 pb-28 pt-6 md:max-w-2xl md:px-8 md:pt-10">
        {{ $slot }}
    </main>

    {{-- Floating tourist nav. Black pill, fixed bottom-center, immer monochrom
         (NICHT accent — die Nav bleibt neutral, accent ist nur für Vendor-CTAs). --}}
    <nav
        x-data="{
            showTop: false,
            init() {
                this.update();
                window.addEventListener('scroll', () => this.update(), { passive: true });
            },
            update() {
                this.showTop = window.scrollY > 240;
            },
            scrollTop() {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },
        }"
        class="pointer-events-none fixed inset-x-0 bottom-5 z-50 flex justify-center px-4"
        aria-label="{{ __('Quick navigation') }}"
    >
        <ul class="pointer-events-auto flex items-center gap-1 rounded-full bg-ink px-2 py-2 shadow-[0_10px_30px_rgba(0,0,0,0.3)]">
            {{-- Top — nur wenn etwas gescrollt --}}
            <li x-show="showTop" x-cloak x-transition.opacity>
                <button
                    type="button"
                    x-on:click="scrollTop()"
                    class="flex h-12 w-12 items-center justify-center rounded-full text-canvas/50 transition hover:text-canvas"
                    aria-label="{{ __('Back to top') }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                        <path d="M12 19V5M5 12l7-7 7 7"/>
                    </svg>
                </button>
            </li>

            {{-- Feed home --}}
            <li>
                <a
                    href="{{ url('/'.($vendor['slug'] ?? '')) }}"
                    @class([
                        'flex h-12 w-12 items-center justify-center rounded-full transition',
                        'bg-canvas text-ink' => ($page ?? 'home') === 'home',
                        'text-canvas/50 hover:text-canvas' => ($page ?? 'home') !== 'home',
                    ])
                    aria-label="{{ __('Feed') }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <path d="M9 22V12h6v10"/>
                    </svg>
                </a>
            </li>

            {{-- Pay --}}
            @if (! empty($vendor['slug']))
                <li>
                    <a
                        href="{{ url('/'.$vendor['slug'].'/pay') }}"
                        @class([
                            'flex h-12 w-12 items-center justify-center rounded-full transition',
                            'bg-canvas text-ink' => ($page ?? '') === 'pay',
                            'text-canvas/50 hover:text-canvas' => ($page ?? '') !== 'pay',
                        ])
                        aria-label="{{ __('Pay') }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                            <rect x="2" y="5" width="20" height="14" rx="2"/>
                            <line x1="2" y1="10" x2="22" y2="10"/>
                        </svg>
                    </a>
                </li>
            @endif

            {{-- Contact — dedicated chat page mit Cookie-Persistenz --}}
            @if (! empty($vendor['slug']))
                <li>
                    <a
                        href="{{ route('public.contact', ['vendor' => $vendor['slug']]) }}"
                        @class([
                            'flex h-12 w-12 items-center justify-center rounded-full transition',
                            'bg-canvas text-ink' => ($page ?? '') === 'contact',
                            'text-canvas/50 hover:text-canvas' => ($page ?? '') !== 'contact',
                        ])
                        aria-label="{{ __('Contact') }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                    </a>
                </li>
            @endif
        </ul>
    </nav>

    @vite(['resources/js/app.js'])
</body>
</html>
