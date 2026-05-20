<x-layouts::auth :title="__('Forgot password')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Forgot your password?')" :description="__('No worries — we\'ll email you a link to set a new one.')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                type="email"
                required
                autofocus
                placeholder="email@example.com"
            />

            <flux:button variant="primary" type="submit" class="w-full" data-test="email-password-reset-link-button">
                {{ __('Send reset link') }}
            </flux:button>
        </form>

        <div class="space-x-1 text-center text-caption text-muted rtl:space-x-reverse">
            <span>{{ __('Remembered it?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Back to sign in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
