<div class="space-y-8">
    <header class="space-y-2">
        <p class="text-caption uppercase tracking-wide text-muted">{{ __('Contact') }}</p>
        <h1 class="text-display text-text">
            {{ __('Message :name', ['name' => $vendor['name'] ?? 'us']) }}
        </h1>
        <p class="text-body text-muted">
            {{ __('We translate both ways. Write in your language — we deliver it in :locale.', [
                'locale' => strtoupper($vendor['locale'] ?? 'th'),
            ]) }}
        </p>
    </header>

    <section class="rounded-lg bg-surface p-6">
        <form
            method="POST"
            action="{{ route('vendor.contact', ['vendor' => $slug]) }}"
            class="flex flex-col gap-3"
        >
            @csrf
            <input type="hidden" name="locale" value="{{ app()->getLocale() }}" />

            <input
                type="text"
                name="name"
                placeholder="{{ __('Name (optional)') }}"
                value="{{ old('name') }}"
                class="rounded-sm border-[1.5px] border-line bg-canvas px-4 py-3 text-body text-text placeholder:text-soft-muted focus:border-ink focus:outline-none"
            />

            <input
                type="email"
                name="email"
                placeholder="{{ __('Email (optional, for notifications)') }}"
                value="{{ old('email') }}"
                class="rounded-sm border-[1.5px] border-line bg-canvas px-4 py-3 text-body text-text placeholder:text-soft-muted focus:border-ink focus:outline-none"
            />

            <textarea
                name="message"
                rows="5"
                required
                placeholder="{{ __('Your message…') }}"
                class="rounded-sm border-[1.5px] border-line bg-canvas px-4 py-3 text-body text-text placeholder:text-soft-muted focus:border-ink focus:outline-none"
            >{{ old('message') }}</textarea>

            @error('message')
                <p class="text-caption text-[#B5564D]">{{ $message }}</p>
            @enderror

            <button
                type="submit"
                class="mt-2 rounded-full bg-accent px-5 py-3 text-label text-canvas transition hover:opacity-90"
            >
                {{ __('Start conversation') }}
            </button>
        </form>
    </section>

    <p class="text-caption text-soft-muted">
        {{ __('After you send your first message, this page becomes your private chat. Keep the link or bookmark it to return.') }}
    </p>
</div>
