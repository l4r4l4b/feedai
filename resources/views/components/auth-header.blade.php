@props([
    'title',
    'description' => null,
])

<header class="flex w-full flex-col text-center gap-2">
    <h1 class="text-display text-ink">{{ $title }}</h1>
    @if ($description)
        <p class="text-body text-muted">{{ $description }}</p>
    @endif
</header>
