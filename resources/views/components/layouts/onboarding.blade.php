@props(['title' => 'Onboarding'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $title }} — FeedAI</title>
    @vite(['resources/css/app.css'])
    @fluxAppearance
    @livewireStyles
</head>
<body class="h-full bg-canvas text-ink antialiased">
    {{ $slot }}

    @fluxScripts
    @livewireScripts
</body>
</html>
