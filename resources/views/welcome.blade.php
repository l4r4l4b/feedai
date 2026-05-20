<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head', ['title' => 'FeedAI — A mini AI-driven feed for micro-businesses'])
</head>
<body class="bg-canvas text-text antialiased">
    {{-- ============================== Nav ============================== --}}
    <nav class="mx-auto flex w-full max-w-6xl items-center justify-between px-5 py-5 md:px-8 md:py-7">
        <a href="/" class="flex items-center gap-2 text-title text-ink">
            <span class="inline-block h-2 w-2 rounded-full bg-live"></span>
            FeedAI
        </a>
        <div class="flex items-center gap-3">
            @auth
                <a href="{{ route('dashboard') }}"
                   class="rounded-md bg-ink px-4 py-2 text-label text-canvas transition hover:opacity-90">
                    Dashboard
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="hidden rounded-md border border-line px-4 py-2 text-label text-ink transition hover:border-ink/40 md:inline-block">
                    Sign in
                </a>
                <a href="{{ route('register') }}"
                   class="rounded-md bg-ink px-4 py-2 text-label text-canvas transition hover:opacity-90">
                    Become a vendor
                </a>
            @endauth
        </div>
    </nav>

    <main class="mx-auto w-full max-w-6xl px-5 pb-24 md:px-8">

        {{-- ============================== Hero ============================== --}}
        <section class="flex flex-col gap-10 pt-12 md:pt-24">
            <div class="flex flex-col gap-6 max-w-3xl">
                <span class="inline-flex w-fit items-center gap-2 rounded-full border border-line bg-surface px-3 py-1 text-caption uppercase tracking-wide text-muted">
                    <span class="inline-block h-1.5 w-1.5 rounded-full bg-live"></span>
                    Built for SEABW Vibe Coding Hackathon
                </span>

                <h1 class="text-display md:text-display-lg text-ink">
                    The fastest way for a street vendor to show up online — without writing a single line of code.
                </h1>

                <p class="text-body text-muted max-w-2xl">
                    FeedAI turns a five-minute chat with an AI in Thai into a polished, mobile-first vendor page,
                    auto-translated for tourists, with a built-in chat bridge and direct payment options.
                    No website. No marketing. No technical skills required.
                </p>

                <div class="flex flex-wrap gap-3 pt-2">
                    <a href="/demo"
                       class="rounded-md bg-ink px-5 py-3 text-label text-canvas transition hover:opacity-90">
                        Open the live demo →
                    </a>
                    <a href="{{ route('register') }}"
                       class="rounded-md border border-line bg-canvas px-5 py-3 text-label text-ink transition hover:border-ink/40">
                        Become a vendor
                    </a>
                </div>
            </div>
        </section>

        {{-- =========================== The problem =========================== --}}
        <section class="pt-24 md:pt-32">
            <div class="flex flex-col gap-3 mb-10">
                <span class="text-caption uppercase tracking-wide text-muted">The problem</span>
                <h2 class="text-section text-ink max-w-2xl">
                    Three things keep micro-businesses invisible to the people walking right past them.
                </h2>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-lg border border-line bg-canvas p-6">
                    <p class="text-caption uppercase tracking-wide text-muted">Digital visibility</p>
                    <h3 class="mt-3 text-title text-ink">No website, no marketing.</h3>
                    <p class="mt-3 text-body text-muted">
                        Street vendors and tour guides have phenomenal products and zero web presence.
                        Building a site is too technical, too slow, too expensive.
                    </p>
                </div>

                <div class="rounded-lg border border-line bg-canvas p-6">
                    <p class="text-caption uppercase tracking-wide text-muted">Language barrier</p>
                    <h3 class="mt-3 text-title text-ink">Vendors work in Thai. Tourists read German or English.</h3>
                    <p class="mt-3 text-body text-muted">
                        Even when the page exists, the language gap blocks the conversation
                        the vendor needs to actually convert curiosity into a sale.
                    </p>
                </div>

                <div class="rounded-lg border border-line bg-canvas p-6">
                    <p class="text-caption uppercase tracking-wide text-muted">Payment friction</p>
                    <h3 class="mt-3 text-title text-ink">Card payments require infrastructure they don't have.</h3>
                    <p class="mt-3 text-body text-muted">
                        No POS terminal, no merchant account, often no bank-side onboarding.
                        Tourists who want to tap and go simply walk away.
                    </p>
                </div>
            </div>
        </section>

        {{-- =========================== How it works =========================== --}}
        <section class="pt-24 md:pt-32">
            <div class="flex flex-col gap-3 mb-10">
                <span class="text-caption uppercase tracking-wide text-muted">How it works</span>
                <h2 class="text-section text-ink max-w-2xl">
                    Four steps. The vendor does step one in their own language. The platform handles the rest.
                </h2>
            </div>

            <ol class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <li class="rounded-lg border border-line bg-canvas p-6">
                    <span class="text-caption uppercase tracking-wide text-muted">Step 01</span>
                    <h3 class="mt-3 text-title text-ink">Chat with the AI</h3>
                    <p class="mt-3 text-body text-muted">
                        The AI asks what the vendor sells, where they are, what they look like.
                        Voice or text, in Thai.
                    </p>
                </li>
                <li class="rounded-lg border border-line bg-canvas p-6">
                    <span class="text-caption uppercase tracking-wide text-muted">Step 02</span>
                    <h3 class="mt-3 text-title text-ink">A feed appears</h3>
                    <p class="mt-3 text-body text-muted">
                        Hero, menu, opening hours, contact buttons. Mobile-first.
                        Live at <code class="font-mono">feedai/{slug}</code>.
                    </p>
                </li>
                <li class="rounded-lg border border-line bg-canvas p-6">
                    <span class="text-caption uppercase tracking-wide text-muted">Step 03</span>
                    <h3 class="mt-3 text-title text-ink">Auto-translated</h3>
                    <p class="mt-3 text-body text-muted">
                        Every component is translated to English and German on save.
                        Tourists browse in their language without the vendor lifting a finger.
                    </p>
                </li>
                <li class="rounded-lg border border-line bg-canvas p-6">
                    <span class="text-caption uppercase tracking-wide text-muted">Step 04</span>
                    <h3 class="mt-3 text-title text-ink">Chat &amp; pay</h3>
                    <p class="mt-3 text-body text-muted">
                        Tourists message in their language and pay in their preferred way.
                        Both sides see their own language — translation is invisible.
                    </p>
                </li>
            </ol>
        </section>

        {{-- =========================== Live demo =========================== --}}
        <section class="pt-24 md:pt-32">
            <div class="rounded-xl border border-line bg-surface p-8 md:p-12">
                <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
                    <div class="flex flex-col gap-3 max-w-xl">
                        <span class="inline-flex w-fit items-center gap-2 rounded-full bg-canvas px-3 py-1 text-caption uppercase tracking-wide text-muted">
                            <span class="inline-block h-1.5 w-1.5 rounded-full bg-live"></span>
                            Live
                        </span>
                        <h2 class="text-section text-ink">
                            Meet Mae Som — Pad Thai on Khao San Road since 1987.
                        </h2>
                        <p class="text-body text-muted">
                            A fully built example vendor with hero, menu, opening hours,
                            a contact bridge, and three payment methods. Click around like a tourist would.
                        </p>
                    </div>
                    <a href="/demo"
                       class="rounded-md bg-ink px-5 py-3 text-label text-canvas transition hover:opacity-90 md:shrink-0">
                        See Mae Som's feed →
                    </a>
                </div>
            </div>
        </section>

        {{-- =========================== Killer feature: Chat Bridge =========================== --}}
        <section class="pt-24 md:pt-32 grid gap-10 md:grid-cols-2">
            <div class="flex flex-col gap-4">
                <span class="text-caption uppercase tracking-wide text-muted">Killer feature</span>
                <h2 class="text-section text-ink">The Contact Bridge.</h2>
                <p class="text-body text-muted">
                    A tourist sends a message in English. The vendor reads it in Thai. Replies in Thai.
                    The tourist sees a clean English answer minutes later. Neither side ever sees the other's language.
                </p>
                <p class="text-body text-muted">
                    Both sides participate in the conversation through their own dashboards.
                    Email notifications nudge them when there's something new. The translation layer is invisible.
                </p>
            </div>

            <div class="rounded-lg border border-line bg-canvas p-6">
                <div class="flex flex-col gap-4">
                    <div class="self-start rounded-lg bg-surface px-4 py-3 max-w-[80%]">
                        <p class="text-caption uppercase tracking-wide text-muted mb-1">Tourist · English</p>
                        <p class="text-body text-ink">Do you have peanut-free pad thai?</p>
                    </div>
                    <div class="self-end rounded-lg bg-ink px-4 py-3 max-w-[80%]">
                        <p class="text-caption uppercase tracking-wide text-soft-muted mb-1">Vendor · Thai</p>
                        <p class="text-body text-canvas">มีค่ะ ไม่ใส่ถั่วได้ บอกตอนสั่งได้เลย</p>
                    </div>
                    <div class="self-start rounded-lg bg-surface px-4 py-3 max-w-[80%]">
                        <p class="text-caption uppercase tracking-wide text-muted mb-1">Tourist sees</p>
                        <p class="text-body text-ink">Yes — we can leave the peanuts out. Just tell us when ordering.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- =========================== Payment philosophy =========================== --}}
        <section class="pt-24 md:pt-32">
            <div class="flex flex-col gap-3 mb-10 max-w-3xl">
                <span class="text-caption uppercase tracking-wide text-muted">Payment philosophy</span>
                <h2 class="text-section text-ink">
                    FeedAI never holds your money.
                </h2>
                <p class="text-body text-muted">
                    We're a payment-options aggregator, not a payment processor. Direct methods stay direct,
                    and even card payments are routed straight to the vendor's own Stripe account.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-lg border border-line bg-canvas p-6">
                    <p class="text-caption uppercase tracking-wide text-muted">Direct · PromptPay</p>
                    <h3 class="mt-3 text-title text-ink">Just a QR code.</h3>
                    <p class="mt-3 text-body text-muted">
                        Vendor stores their PromptPay number, we render the QR.
                        Money flows from the tourist's bank app straight to the vendor.
                    </p>
                </div>
                <div class="rounded-lg border border-line bg-canvas p-6">
                    <p class="text-caption uppercase tracking-wide text-muted">Direct · Crypto</p>
                    <h3 class="mt-3 text-title text-ink">Just an address.</h3>
                    <p class="mt-3 text-body text-muted">
                        Wallet address plus chain label. Copy, paste, send.
                        We display, the vendor custodies, no wallet-connect theatre.
                    </p>
                </div>
                <div class="rounded-lg border border-line bg-canvas p-6">
                    <p class="text-caption uppercase tracking-wide text-muted">Platform · Card</p>
                    <h3 class="mt-3 text-title text-ink">Stripe Connect Express.</h3>
                    <p class="mt-3 text-body text-muted">
                        Two-minute onboarding gives the vendor their own Stripe account.
                        Destination charges route funds straight through.
                    </p>
                </div>
            </div>
        </section>

        {{-- =========================== Tech CTA =========================== --}}
        <section class="pt-24 md:pt-32">
            <div class="flex flex-col items-start gap-5 md:items-center md:text-center">
                <h2 class="text-section text-ink max-w-2xl">
                    Built in 24 hours. Open source. Run it locally with one command.
                </h2>
                <p class="text-body text-muted max-w-2xl">
                    Laravel 13, Livewire 4, the Laravel AI SDK with Anthropic, Spatie Media,
                    Stripe Connect, Postmark and a lot of opinionated YAML.
                </p>
                <div class="flex flex-wrap gap-3 pt-2">
                    <a href="/demo"
                       class="rounded-md bg-ink px-5 py-3 text-label text-canvas transition hover:opacity-90">
                        Try the demo →
                    </a>
                    <a href="{{ route('register') }}"
                       class="rounded-md border border-line bg-canvas px-5 py-3 text-label text-ink transition hover:border-ink/40">
                        Onboard a vendor
                    </a>
                </div>
            </div>
        </section>
    </main>

    {{-- ============================== Footer ============================== --}}
    <footer class="border-t border-line">
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-3 px-5 py-8 md:flex-row md:items-center md:justify-between md:px-8">
            <div class="flex items-center gap-2 text-label text-ink">
                <span class="inline-block h-2 w-2 rounded-full bg-live"></span>
                FeedAI
            </div>
            <p class="text-caption text-muted">
                Submission for SEABW Vibe Coding Hackathon · 24h solo build · Bangkok, May 2026
            </p>
        </div>
    </footer>
</body>
</html>
