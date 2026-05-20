@php
    use App\Support\Locale;
    $defaultLocale = old('locale', request()->getPreferredLanguage(Locale::SUPPORTED) ?: 'en');
    if (! Locale::isSupported($defaultLocale)) {
        $defaultLocale = 'en';
    }
@endphp
<x-layouts::auth :title="__('Register')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Become a vendor')" :description="__('Two minutes to set up. No technical skills needed.')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf
            <!-- Name -->
            <flux:input
                name="name"
                :label="__('Name')"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                :placeholder="__('Full name')"
            />

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Password')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('Confirm password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Confirm password')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            {{-- Source language, the language the vendor will *write* their
                 feed in. Translations to the other two are generated
                 automatically. Defaulted from the browser; vendor can change. --}}
            <flux:field>
                <flux:label>{{ __('Your language') }}</flux:label>
                <flux:description>{{ __('You write your feed in this language; we translate it to the others automatically.') }}</flux:description>
                <flux:select name="locale">
                    <flux:select.option value="en" :selected="$defaultLocale === 'en'">English</flux:select.option>
                    <flux:select.option value="de" :selected="$defaultLocale === 'de'">Deutsch</flux:select.option>
                    <flux:select.option value="th" :selected="$defaultLocale === 'th'">ไทย (Thai)</flux:select.option>
                </flux:select>
            </flux:field>

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                    {{ __('Create account') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 text-center text-caption text-muted rtl:space-x-reverse">
            <span>{{ __('Already a vendor?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Sign in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
