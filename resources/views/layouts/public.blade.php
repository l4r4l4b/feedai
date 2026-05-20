<!DOCTYPE html>
<html lang="{{ $vendor['locale'] ?? 'th' }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex" />
    <title>{{ $vendor['name'] ?? 'FeedAI' }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-canvas text-ink antialiased">
    <main class="mx-auto min-h-screen w-full max-w-md px-5 pb-16 pt-6 md:max-w-2xl md:px-8 md:pt-10">
        {{ $slot }}
    </main>
</body>
</html>
