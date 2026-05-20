<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-surface text-text antialiased">
        <div class="flex min-h-svh flex-col items-center justify-center gap-8 p-6 md:p-10">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-title text-ink" wire:navigate>
                <span class="inline-block h-2 w-2 rounded-full bg-live"></span>
                FeedAI
            </a>

            <div class="w-full max-w-md rounded-lg border border-line bg-canvas px-8 py-10 md:px-10">
                {{ $slot }}
            </div>

            <p class="text-caption text-muted">
                A mini AI-driven feed for micro-businesses · Bangkok
            </p>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
