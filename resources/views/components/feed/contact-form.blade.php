@props([
    'vendorSlug',
    'sectionLabel' => null,
    'title' => 'Kontakt',
    'intro' => null,
    'submitLabel' => 'Senden',
    'locale' => null,
])

<section {{ $attributes->class(['rounded-card border border-line bg-soft p-6']) }} id="contact-form">
    @if ($sectionLabel)
        <p class="mb-3 font-mono text-mono-label uppercase text-muted">
            {{ $sectionLabel }}
        </p>
    @endif

    <h2 class="text-section text-ink">{{ $title }}</h2>

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
            class="rounded-input border border-line bg-card px-4 py-3 text-body text-ink placeholder:text-soft-ink"
        />
        <input
            type="email"
            name="email"
            placeholder="Email (optional, für Benachrichtigung)"
            value="{{ old('email') }}"
            class="rounded-input border border-line bg-card px-4 py-3 text-body text-ink placeholder:text-soft-ink"
        />
        <textarea
            name="message"
            rows="4"
            required
            placeholder="Deine Nachricht…"
            class="rounded-input border border-line bg-card px-4 py-3 text-body text-ink placeholder:text-soft-ink"
        >{{ old('message') }}</textarea>

        @error('message')
            <p class="text-caption text-error">{{ $message }}</p>
        @enderror

        <button
            type="submit"
            class="mt-1 rounded-button bg-accent px-5 py-3 text-caption font-medium text-card transition hover:opacity-90"
        >
            {{ $submitLabel }}
        </button>

        <p class="mt-1 font-mono text-[10px] uppercase tracking-wider text-soft-ink">
            Wird automatisch in die Sprache des Vendors übersetzt
        </p>
    </form>
</section>
