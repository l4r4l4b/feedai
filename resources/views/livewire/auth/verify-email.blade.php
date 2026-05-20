<x-layouts::auth :title="__('Email verification')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Check your inbox')" :description="__('We just sent you a verification link. Click it to activate your account.')" />

        @if (session('status') == 'verification-link-sent')
            <p class="text-center text-caption text-[#5B7D5F]">
                {{ __('A new verification link is on its way.') }}
            </p>
        @endif

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <flux:button type="submit" variant="primary" class="w-full">
                {{ __('Resend verification email') }}
            </flux:button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="text-center">
            @csrf
            <flux:button variant="ghost" type="submit" class="cursor-pointer text-caption" data-test="logout-button">
                {{ __('Sign out') }}
            </flux:button>
        </form>
    </div>
</x-layouts::auth>
