<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-canvas text-text antialiased">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-line bg-surface">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                    @if (auth()->user()?->isVendor())
                        @php
                            $unreadCount = \App\Models\Message::whereHas('conversation', fn ($q) => $q->where('vendor_id', auth()->user()->vendor?->id))
                                ->where('sender', 'tourist')
                                ->whereNull('read_at')
                                ->count();
                        @endphp
                        <flux:sidebar.item icon="inbox" :href="route('inbox')" :current="request()->routeIs('inbox*')" wire:navigate>
                            {{ __('Inbox') }}
                            @if ($unreadCount > 0)
                                <flux:badge size="sm" color="zinc" class="ml-auto">{{ $unreadCount }}</flux:badge>
                            @endif
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="paint-brush" :href="route('dashboard.accent')" :current="request()->routeIs('dashboard.accent')" wire:navigate>
                            {{ __('Accent color') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="credit-card" :href="route('dashboard.payments.settings')" :current="request()->routeIs('dashboard.payments.*')" wire:navigate>
                            {{ __('Payment methods') }}
                        </flux:sidebar.item>
                        @if (auth()->user()?->vendor)
                            <flux:sidebar.item icon="arrow-top-right-on-square" :href="url('/'.auth()->user()->vendor->slug)" target="_blank">
                                {{ __('Public feed') }}
                            </flux:sidebar.item>
                        @endif
                    @endif
                </flux:sidebar.group>

                @if (auth()->user()?->isAdmin())
                    <flux:sidebar.group :heading="__('Admin')" class="grid">
                        <flux:sidebar.item icon="squares-2x2" :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')" wire:navigate>
                            {{ __('Overview') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="users" :href="route('admin.vendors')" :current="request()->routeIs('admin.vendors')" wire:navigate>
                            {{ __('Vendors') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="credit-card" :href="route('admin.payments')" :current="request()->routeIs('admin.payments')" wire:navigate>
                            {{ __('Payments') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="chat-bubble-left-right" :href="route('admin.conversations')" :current="request()->routeIs('admin.conversations')" wire:navigate>
                            {{ __('Conversations') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="globe-alt" :href="route('home')" wire:navigate>
                    {{ __('Marketing site') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
