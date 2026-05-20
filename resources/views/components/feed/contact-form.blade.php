@props([
    'vendorSlug',
    'sectionLabel' => null,
    'title' => 'Contact',
    'intro' => null,
    'submitLabel' => 'Send',
    'locale' => null,
])

<section {{ $attributes->class(['rounded-lg bg-surface p-6']) }} id="contact-form">
    @if ($sectionLabel)
        <p class="mb-3 text-caption uppercase tracking-wide text-muted">
            {{ $sectionLabel }}
        </p>
    @endif

    <h2 class="text-section text-text">{{ $title }}</h2>

    @if ($intro)
        <p class="mt-2 text-caption text-muted">{{ $intro }}</p>
    @endif

    <form
        method="POST"
        action="{{ route('vendor.contact', ['vendor' => $vendorSlug]) }}"
        class="mt-5 flex flex-col gap-3"
    >
        @csrf
        <input type="hidden" name="locale" value="{{ $locale ?? app()->getLocale() }}" />

        <input
            type="text"
            name="name"
            placeholder="Name (optional)"
            value="{{ old('name') }}"
            class="rounded-sm border-[1.5px] border-line bg-canvas px-4 py-3 text-body text-text placeholder:text-soft-muted focus:border-ink focus:outline-none"
        />
        <input
            type="email"
            name="email"
            placeholder="Email (optional, for notifications)"
            value="{{ old('email') }}"
            class="rounded-sm border-[1.5px] border-line bg-canvas px-4 py-3 text-body text-text placeholder:text-soft-muted focus:border-ink focus:outline-none"
        />
        <textarea
            name="message"
            rows="4"
            required
            placeholder="Your message…"
            class="rounded-sm border-[1.5px] border-line bg-canvas px-4 py-3 text-body text-text placeholder:text-soft-muted focus:border-ink focus:outline-none"
        >{{ old('message') }}</textarea>

        @error('message')
            <p class="text-caption text-[#B5564D]">{{ $message }}</p>
        @enderror

        <button
            type="submit"
            class="mt-1 rounded-full bg-accent px-5 py-3 text-label text-canvas transition hover:opacity-90"
        >
            {{ $submitLabel }}
        </button>

        <p class="mt-1 text-caption text-soft-muted">
            Auto-translated both ways, the vendor writes in their language, you write in yours.
        </p>
    </form>
</section>
