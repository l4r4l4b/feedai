@props(['title' => 'Pay'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $title }} — FeedAI</title>
    @include('partials.force-light')
    @vite(['resources/css/app.css'])
    @livewireStyles
</head>
<body class="bg-canvas text-ink antialiased">
    <main class="mx-auto min-h-screen w-full max-w-md px-5 pb-16 pt-6 md:max-w-xl md:px-8 md:pt-10">
        {{ $slot }}
    </main>

    @fluxScripts
    @livewireScripts
</body>
</html>
