@props([
    'sidebar' => false,
])

@php
    $component = $sidebar ? 'flux:sidebar.brand' : 'flux:brand';
@endphp

<{{ $component }} name="FeedAI" {{ $attributes }}>
    <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-ink text-canvas">
        <x-app-logo-icon class="size-3" />
    </x-slot>
</{{ $component }}>
