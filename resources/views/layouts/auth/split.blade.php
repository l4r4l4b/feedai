<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-canvas text-text antialiased">
        <div class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
            {{-- Left panel: FeedAI marketing copy on dark ink --}}
            <div class="relative hidden h-full flex-col p-10 text-canvas lg:flex">
                <div class="absolute inset-0 bg-ink"></div>

                <a href="{{ route('home') }}" class="relative z-20 flex items-center gap-2 text-title" wire:navigate>
                    <span class="inline-block h-2 w-2 rounded-full bg-live"></span>
                    FeedAI
                </a>

                <div class="relative z-20 mt-auto flex flex-col gap-3">
                    <p class="text-section text-canvas">
                        Five-minute chat. Polished feed. Auto-translated. Ready for tourists.
                    </p>
                    <p class="text-body text-soft-muted">
                        Built for street vendors and small service providers in emerging markets.
                    </p>
                </div>
            </div>

            {{-- Right panel: the actual auth form --}}
            <div class="w-full lg:p-8">
                <div class="mx-auto flex w-full flex-col gap-6 sm:w-[360px]">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 text-title text-ink lg:hidden" wire:navigate>
                        <span class="inline-block h-2 w-2 rounded-full bg-live"></span>
                        FeedAI
                    </a>
                    {{ $slot }}
                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
