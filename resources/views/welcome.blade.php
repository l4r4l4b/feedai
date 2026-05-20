<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head', ['title' => 'FeedAI — A mini AI-driven feed for micro-businesses'])
</head>
<body class="bg-canvas text-text antialiased">
    {{-- ============================== Nav ============================== --}}
    <nav class="mx-auto flex w-full max-w-6xl items-center justify-between px-5 py-5 md:px-8 md:py-7">
        <a href="/" class="flex items-center gap-2 text-title text-ink">
            <span class="flex aspect-square size-7 items-center justify-center rounded-md bg-ink text-canvas">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="size-4" aria-hidden="true">
                    <path d="M5 7h11" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                    <path d="M5 12h8" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                    <path d="M5 17h5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                    <circle cx="18" cy="17" r="2.2" fill="currentColor"/>
                </svg>
            </span>
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
        <section class="grid items-center gap-12 pt-10 md:grid-cols-[1.1fr_0.9fr] md:pt-20">
            <div class="flex flex-col gap-6">
                <span class="inline-flex w-fit items-center gap-2 rounded-full border border-line bg-surface px-3 py-1 text-caption uppercase tracking-wide text-muted">
                    <span class="inline-block h-1.5 w-1.5 rounded-full bg-live"></span>
                    Built for SEABW Vibe Coding Hackathon
                </span>

                <h1 class="text-display md:text-display-lg text-ink">
                    A polished feed for every street-corner business — built in a five-minute chat.
                </h1>

                <p class="max-w-xl text-body text-muted">
                    FeedAI turns one conversation with an AI into a mobile-first vendor page,
                    auto-translated for tourists, with a built-in chat bridge and direct payment options.
                    No website. No marketing copy. No technical skills required.
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

            {{-- Phone-frame with a real Hero component as preview --}}
            <div class="relative mx-auto w-full max-w-[300px]">
                <div class="absolute -inset-6 rounded-[42px] bg-gradient-to-br from-surface to-canvas blur-2xl opacity-60" aria-hidden="true"></div>
                <div class="relative rounded-[36px] border-4 border-ink bg-canvas p-2 shadow-[0_24px_48px_rgba(15,23,42,0.18)]">
                    <div class="overflow-hidden rounded-[28px] bg-canvas">
                        <x-feed.hero
                            image="https://images.unsplash.com/photo-1559314809-0d155014e29e?w=900&q=80"
                            title="Mae Som Pad Thai"
                            location="Khao San Road · Bangkok"
                            rating="4.95 (203)"
                        />
                    </div>
                </div>
            </div>
        </section>

        {{-- =========================== The problem =========================== --}}
        <section class="pt-24 md:pt-32">
            <div class="mb-10 flex flex-col gap-3">
                <span class="text-caption uppercase tracking-wide text-muted">The problem</span>
                <h2 class="max-w-2xl text-section text-ink">
                    Three things keep micro-businesses invisible to the people walking right past them.
                </h2>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-lg border border-line bg-canvas p-6">
                    <p class="text-caption uppercase tracking-wide text-muted">Digital visibility</p>
                    <h3 class="mt-3 text-title text-ink">No website, no marketing.</h3>
                    <p class="mt-3 text-body text-muted">
                        Phenomenal products and zero web presence.
                        Building a site is too technical, too slow, too expensive.
                    </p>
                </div>
                <div class="rounded-lg border border-line bg-canvas p-6">
                    <p class="text-caption uppercase tracking-wide text-muted">Language barrier</p>
                    <h3 class="mt-3 text-title text-ink">Vendor speaks Thai. Tourist reads German.</h3>
                    <p class="mt-3 text-body text-muted">
                        Even with a page, the language gap blocks the conversation
                        that converts curiosity into a sale.
                    </p>
                </div>
                <div class="rounded-lg border border-line bg-canvas p-6">
                    <p class="text-caption uppercase tracking-wide text-muted">Payment friction</p>
                    <h3 class="mt-3 text-title text-ink">Card terminals they don't have.</h3>
                    <p class="mt-3 text-body text-muted">
                        No POS, no merchant account.
                        Tourists who want to tap-and-go simply walk away.
                    </p>
                </div>
            </div>
        </section>

        {{-- =========================== Eight blocks =========================== --}}
        <section class="pt-24 md:pt-32">
            <div class="mb-10 flex flex-col gap-3">
                <span class="text-caption uppercase tracking-wide text-muted">What you get</span>
                <h2 class="max-w-3xl text-section text-ink">
                    Eight content blocks the AI assembles from one conversation.
                </h2>
                <p class="max-w-2xl text-body text-muted">
                    Same building blocks, different vendor — every feed feels custom because the AI
                    rewrites every line in magazine voice instead of pasting verbatim.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                {{-- About --}}
                <div class="flex flex-col">
                    <p class="mb-2 text-caption font-semibold uppercase tracking-wide text-muted">About</p>
                    <div class="rounded-lg border border-line bg-canvas p-5">
                        <x-feed.about section-label="ABOUT MAE SOM" body="Thirty years at the same wok. Mae Som still grinds the peanuts herself every morning and boils tamarind concentrate twice a week. Three classics, one recipe, no shortcuts." />
                    </div>
                </div>

                {{-- Opening hours --}}
                <div class="flex flex-col">
                    <p class="mb-2 text-caption font-semibold uppercase tracking-wide text-muted">Opening hours</p>
                    <div class="rounded-lg border border-line bg-canvas p-5">
                        <x-feed.opening-hours
                            section-label="HOURS"
                            :hours="[
                                ['days' => 'Mon–Fri', 'time' => '17:00–23:00'],
                                ['days' => 'Sat', 'time' => '17:00–01:00'],
                                ['days' => 'Sun', 'time' => 'Closed'],
                            ]"
                            note="Closed on public holidays."
                        />
                    </div>
                </div>

                {{-- Contact buttons --}}
                <div class="flex flex-col">
                    <p class="mb-2 text-caption font-semibold uppercase tracking-wide text-muted">Contact</p>
                    <div class="rounded-lg border border-line bg-canvas p-5">
                        <x-feed.contact-buttons
                            section-label="ASK US"
                            :buttons="[
                                ['channel' => 'whatsapp', 'value' => '66812345678', 'label' => 'WhatsApp'],
                                ['channel' => 'line', 'value' => 'maesom', 'label' => 'LINE'],
                                ['channel' => 'call', 'value' => '+66812345678', 'label' => 'Call'],
                                ['channel' => 'facebook', 'value' => 'maesompadthai', 'label' => 'Facebook'],
                            ]"
                        />
                    </div>
                </div>

                {{-- Highlight card --}}
                <div class="flex flex-col">
                    <p class="mb-2 text-caption font-semibold uppercase tracking-wide text-muted">Notice</p>
                    <div class="rounded-lg border border-line bg-canvas p-5">
                        <x-feed.highlight-card
                            icon="💵"
                            headline="Cash only"
                            body="ATM 50m away. PromptPay works too."
                        />
                    </div>
                </div>

                {{-- Testimonial --}}
                <div class="flex flex-col">
                    <p class="mb-2 text-caption font-semibold uppercase tracking-wide text-muted">Testimonial</p>
                    <div class="rounded-lg border border-line bg-canvas p-5">
                        <x-feed.testimonial
                            quote="Best Pad Thai of the trip. Mae Som laughs so warmly that we almost wanted to stay."
                            author="Lena & Tom"
                            role="Berlin, Germany"
                        />
                    </div>
                </div>

                {{-- Pay now trigger --}}
                <div class="flex flex-col">
                    <p class="mb-2 text-caption font-semibold uppercase tracking-wide text-muted">Pay button</p>
                    <div class="rounded-lg border border-line bg-canvas p-5">
                        <x-feed.pay-now-trigger
                            label="PAY"
                            title="Pay right here at the stall"
                            url="#"
                        />
                    </div>
                </div>
            </div>

            <p class="mt-8 text-center text-caption text-muted">
                Plus menu, gallery, location, FAQ, CTA, image dividers, contact form, and a single-service spotlight.
            </p>
        </section>

        {{-- =========================== Who's it for =========================== --}}
        <section class="pt-24 md:pt-32">
            <div class="mb-10 flex flex-col gap-3">
                <span class="text-caption uppercase tracking-wide text-muted">Who it's for</span>
                <h2 class="max-w-2xl text-section text-ink">
                    Three kinds of micro-businesses. One product.
                </h2>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <div class="flex flex-col rounded-lg border border-line bg-canvas overflow-hidden">
                    <div class="aspect-[3/2] bg-surface" style="background-image: url('https://images.unsplash.com/photo-1559314809-0d155014e29e?w=800&q=70'); background-size: cover; background-position: center;"></div>
                    <div class="flex flex-col gap-2 p-5">
                        <p class="text-caption uppercase tracking-wide text-muted">Street food</p>
                        <h3 class="text-title text-ink">The stall on the corner since 1987.</h3>
                        <p class="text-body text-muted">Menu, hours, location, contact. A QR code at the table replaces every business card they never printed.</p>
                    </div>
                </div>

                <div class="flex flex-col rounded-lg border border-line bg-canvas overflow-hidden">
                    <div class="aspect-[3/2] bg-surface" style="background-image: url('https://images.unsplash.com/photo-1552465011-b4e21bf6e79a?w=800&q=70'); background-size: cover; background-position: center;"></div>
                    <div class="flex flex-col gap-2 p-5">
                        <p class="text-caption uppercase tracking-wide text-muted">Tours & transfers</p>
                        <h3 class="text-title text-ink">The tuk-tuk driver with 12 years of shortcuts.</h3>
                        <p class="text-body text-muted">Tour menu with prices, durations, photos of past trips. Direct WhatsApp booking. Card payment for the airport run.</p>
                    </div>
                </div>

                <div class="flex flex-col rounded-lg border border-line bg-canvas overflow-hidden">
                    <div class="aspect-[3/2] bg-surface" style="background-image: url('https://images.unsplash.com/photo-1600334129128-685c5582fd35?w=800&q=70'); background-size: cover; background-position: center;"></div>
                    <div class="flex flex-col gap-2 p-5">
                        <p class="text-caption uppercase tracking-wide text-muted">Wellness</p>
                        <h3 class="text-title text-ink">The massage studio with no Google listing.</h3>
                        <p class="text-body text-muted">Service list, opening hours, testimonials, a contact form for appointment requests — translated both ways automatically.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- =========================== How it works =========================== --}}
        <section class="pt-24 md:pt-32">
            <div class="mb-10 flex flex-col gap-3">
                <span class="text-caption uppercase tracking-wide text-muted">How it works</span>
                <h2 class="max-w-2xl text-section text-ink">
                    Four steps. The vendor does step one in their own language. The platform handles the rest.
                </h2>
            </div>

            <ol class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <li class="rounded-lg border border-line bg-canvas p-6">
                    <span class="text-caption uppercase tracking-wide text-muted">Step 01</span>
                    <h3 class="mt-3 text-title text-ink">Chat with the AI</h3>
                    <p class="mt-3 text-body text-muted">
                        Six quick questions in the vendor's language. They answer in their own words. Photos optional.
                    </p>
                </li>
                <li class="rounded-lg border border-line bg-canvas p-6">
                    <span class="text-caption uppercase tracking-wide text-muted">Step 02</span>
                    <h3 class="mt-3 text-title text-ink">A feed appears</h3>
                    <p class="mt-3 text-body text-muted">
                        Hero, about, menu, hours, contact, CTA, pay button, contact form — assembled live at
                        <code class="font-mono text-caption">feedai/{slug}</code>.
                    </p>
                </li>
                <li class="rounded-lg border border-line bg-canvas p-6">
                    <span class="text-caption uppercase tracking-wide text-muted">Step 03</span>
                    <h3 class="mt-3 text-title text-ink">Auto-translated</h3>
                    <p class="mt-3 text-body text-muted">
                        Every block is translated into the other two languages on save. Tourists browse in theirs without the vendor lifting a finger.
                    </p>
                </li>
                <li class="rounded-lg border border-line bg-canvas p-6">
                    <span class="text-caption uppercase tracking-wide text-muted">Step 04</span>
                    <h3 class="mt-3 text-title text-ink">Chat &amp; pay</h3>
                    <p class="mt-3 text-body text-muted">
                        Tourists message in their own language and pay their preferred way. Both sides see only theirs.
                    </p>
                </li>
            </ol>
        </section>

        {{-- =========================== Live demo =========================== --}}
        <section class="pt-24 md:pt-32">
            <div class="rounded-xl border border-line bg-surface p-8 md:p-12">
                <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
                    <div class="flex max-w-xl flex-col gap-3">
                        <span class="inline-flex w-fit items-center gap-2 rounded-full bg-canvas px-3 py-1 text-caption uppercase tracking-wide text-muted">
                            <span class="inline-block h-1.5 w-1.5 rounded-full bg-live"></span>
                            Live
                        </span>
                        <h2 class="text-section text-ink">
                            Meet Mae Som — Pad Thai on Khao San Road since 1987.
                        </h2>
                        <p class="text-body text-muted">
                            A fully built example vendor with hero, menu, opening hours, a contact bridge, and three payment methods. Click around like a tourist would.
                        </p>
                    </div>
                    <a href="/demo"
                       class="rounded-md bg-ink px-5 py-3 text-label text-canvas transition hover:opacity-90 md:shrink-0">
                        See Mae Som's feed →
                    </a>
                </div>
            </div>
        </section>

        {{-- =========================== Chat Bridge =========================== --}}
        <section class="grid gap-10 pt-24 md:grid-cols-2 md:pt-32">
            <div class="flex flex-col gap-4">
                <span class="text-caption uppercase tracking-wide text-muted">Killer feature</span>
                <h2 class="text-section text-ink">The Contact Bridge.</h2>
                <p class="text-body text-muted">
                    A tourist sends a message in English. The vendor reads it in Thai. Replies in Thai.
                    The tourist sees a clean English answer minutes later. Neither side ever sees the other's language.
                </p>
                <p class="text-body text-muted">
                    Both sides participate through their own dashboards. Email notifications nudge them when there's something new. The translation layer is invisible.
                </p>
            </div>

            <div class="rounded-lg border border-line bg-canvas p-6">
                <div class="flex flex-col gap-4">
                    <div class="max-w-[80%] self-start rounded-lg bg-surface px-4 py-3">
                        <p class="mb-1 text-caption uppercase tracking-wide text-muted">Tourist · English</p>
                        <p class="text-body text-ink">Do you have peanut-free pad thai?</p>
                    </div>
                    <div class="max-w-[80%] self-end rounded-lg bg-ink px-4 py-3">
                        <p class="mb-1 text-caption uppercase tracking-wide text-soft-muted">Vendor · Thai</p>
                        <p class="text-body text-canvas">มีค่ะ ไม่ใส่ถั่วได้ บอกตอนสั่งได้เลย</p>
                    </div>
                    <div class="max-w-[80%] self-start rounded-lg bg-surface px-4 py-3">
                        <p class="mb-1 text-caption uppercase tracking-wide text-muted">Tourist sees</p>
                        <p class="text-body text-ink">Yes — we can leave the peanuts out. Just tell us when ordering.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- =========================== Payment philosophy =========================== --}}
        <section class="pt-24 md:pt-32">
            <div class="mb-10 flex max-w-3xl flex-col gap-3">
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
                        Vendor stores their PromptPay number, we render the QR. Money flows from the tourist's bank app straight to the vendor.
                    </p>
                </div>
                <div class="rounded-lg border border-line bg-canvas p-6">
                    <p class="text-caption uppercase tracking-wide text-muted">Direct · Crypto</p>
                    <h3 class="mt-3 text-title text-ink">Just an address.</h3>
                    <p class="mt-3 text-body text-muted">
                        Wallet address plus chain label, with a QR. Copy, paste, send. We display, the vendor custodies.
                    </p>
                </div>
                <div class="rounded-lg border border-line bg-canvas p-6">
                    <p class="text-caption uppercase tracking-wide text-muted">Platform · Card</p>
                    <h3 class="mt-3 text-title text-ink">Stripe Connect Express.</h3>
                    <p class="mt-3 text-body text-muted">
                        Two-minute onboarding gives the vendor their own Stripe account. Destination charges route funds straight through.
                    </p>
                </div>
            </div>
        </section>

        {{-- =========================== Tech CTA =========================== --}}
        <section class="pt-24 md:pt-32">
            <div class="flex flex-col items-start gap-5 md:items-center md:text-center">
                <h2 class="max-w-2xl text-section text-ink">
                    Built in 24 hours. Open source. Run it locally with one command.
                </h2>
                <p class="max-w-2xl text-body text-muted">
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
                <span class="flex aspect-square size-6 items-center justify-center rounded-md bg-ink text-canvas">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="size-3.5" aria-hidden="true">
                        <path d="M5 7h11" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                        <path d="M5 12h8" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                        <path d="M5 17h5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                        <circle cx="18" cy="17" r="2.2" fill="currentColor"/>
                    </svg>
                </span>
                FeedAI
            </div>
            <p class="text-caption text-muted">
                Submission for SEABW Vibe Coding Hackathon · 24h solo build · Bangkok, May 2026
            </p>
        </div>
    </footer>
</body>
</html>
