<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head', ['title' => 'FeedAI · A mini AI-driven feed for micro-businesses'])
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
        <section class="grid items-center gap-12 pt-10 md:grid-cols-[1fr_1.1fr] md:pt-20">
            <div class="flex flex-col gap-6">
                <span class="inline-flex w-fit items-center gap-2 rounded-full border border-line bg-surface px-3 py-1 text-caption uppercase tracking-wide text-muted">
                    <span class="inline-block h-1.5 w-1.5 rounded-full bg-live"></span>
                    Built for SEABW Vibe Coding Hackathon
                </span>

                <h1 class="text-display text-ink md:text-[52px] md:leading-[1.04] md:tracking-[-0.03em]">
                    A polished feed for every street-corner business, built in a five-minute chat.
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

            {{-- Three-phone fan: front centre, two slightly tilted behind it.
                 Pulls the first three live vendors so the variety is real; falls
                 back to a hand-curated trio if the DB is fresh. --}}
            @php
                $heroFanDefaults = [
                    [
                        'name' => 'Khao San Coffee Lab',
                        'location' => 'Khao San Road · Bangkok',
                        'image' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=900&q=80',
                        'slug' => 'khao-san-coffee',
                    ],
                    [
                        'name' => 'Mae Som Pad Thai',
                        'location' => 'Khao San Road · Bangkok',
                        'image' => 'https://images.unsplash.com/photo-1559314809-0d155014e29e?w=900&q=80',
                        'slug' => 'mae-som',
                    ],
                    [
                        'name' => 'Niran Tuk-Tuk Adventures',
                        'location' => 'Sukhumvit · Bangkok',
                        'image' => 'https://images.unsplash.com/photo-1552465011-b4e21bf6e79a?w=900&q=80',
                        'slug' => 'niran-tuktuk',
                    ],
                ];

                $heroFan = ! empty($liveVendors)
                    ? collect($liveVendors)->take(3)->values()->all()
                    : [];

                foreach ($heroFanDefaults as $default) {
                    if (count($heroFan) >= 3) { break; }
                    $heroFan[] = $default;
                }

                [$leftVendor, $centerVendor, $rightVendor] = array_values($heroFan);
            @endphp

            {{-- Mini-hero tile partial mirrors x-feed.hero (image + location + title
                 overlay) but at thumbnail-appropriate font sizes so titles never wrap
                 inside the small fan phones. --}}
            <div class="relative mx-auto aspect-[5/4] w-full max-w-[560px]">
                {{-- Soft platform shadow under the whole fan --}}
                <div aria-hidden="true" class="absolute inset-x-6 -inset-y-4 rounded-[44px] bg-gradient-to-br from-surface to-canvas opacity-70 blur-2xl"></div>

                {{-- Back-left phone, tilted out --}}
                <div class="absolute left-0 top-12 w-[46%] origin-bottom-right rotate-[-7deg]">
                    <div class="rounded-[28px] border-[3px] border-ink bg-canvas p-1.5 shadow-[0_14px_30px_rgba(15,23,42,0.14)]">
                        <div class="overflow-hidden rounded-[22px] bg-canvas">
                            @include('partials._hero-fan-tile', ['image' => $leftVendor['image'], 'name' => $leftVendor['name'], 'location' => $leftVendor['location'] ?? null])
                        </div>
                    </div>
                </div>

                {{-- Back-right phone, tilted out --}}
                <div class="absolute right-0 top-12 w-[46%] origin-bottom-left rotate-[7deg]">
                    <div class="rounded-[28px] border-[3px] border-ink bg-canvas p-1.5 shadow-[0_14px_30px_rgba(15,23,42,0.14)]">
                        <div class="overflow-hidden rounded-[22px] bg-canvas">
                            @include('partials._hero-fan-tile', ['image' => $rightVendor['image'], 'name' => $rightVendor['name'], 'location' => $rightVendor['location'] ?? null])
                        </div>
                    </div>
                </div>

                {{-- Front-centre phone, slightly bigger + stronger frame --}}
                <div class="absolute left-1/2 top-0 z-10 w-[50%] -translate-x-1/2">
                    <div class="rounded-[30px] border-4 border-ink bg-canvas p-2 shadow-[0_22px_44px_rgba(15,23,42,0.22)]">
                        <div class="overflow-hidden rounded-[22px] bg-canvas">
                            @include('partials._hero-fan-tile', ['image' => $centerVendor['image'], 'name' => $centerVendor['name'], 'location' => $centerVendor['location'] ?? null, 'emphasised' => true])
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- =========================== Live vendors (horizontal scroll) =========================== --}}
        @if (! empty($liveVendors) && count($liveVendors) > 0)
            <section class="pt-24 md:pt-32">
                <div class="mb-10 flex flex-col gap-3">
                    <span class="inline-flex w-fit items-center gap-2 rounded-full border border-line bg-surface px-3 py-1 text-caption uppercase tracking-wide text-muted">
                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-live"></span>
                        Live now · {{ count($liveVendors) }}
                    </span>
                    <h2 class="max-w-2xl text-section text-ink">
                        Real vendors who built their feed.
                    </h2>
                </div>

                {{-- Stays inside the page container — same width as every other
                     section. A right-edge mask fade hints that more cards exist past
                     the visible viewport without breaking the page rhythm. --}}
                <div class="overflow-x-auto pb-2 [mask-image:linear-gradient(to_right,#000_0,#000_calc(100%-56px),transparent_100%)]">
                    <ol class="flex snap-x snap-mandatory gap-4">
                        @foreach ($liveVendors as $vendor)
                            <li class="w-56 shrink-0 snap-start sm:w-64">
                                <a
                                    href="{{ url('/'.$vendor['slug']) }}"
                                    class="group flex h-full flex-col overflow-hidden rounded-lg border border-line bg-canvas transition hover:border-ink hover:shadow-[0_12px_32px_rgba(15,23,42,0.08)]"
                                >
                                    <div class="aspect-[3/4] w-full overflow-hidden bg-surface">
                                        <img
                                            src="{{ $vendor['image'] }}"
                                            alt="{{ $vendor['name'] }}"
                                            loading="lazy"
                                            class="h-full w-full object-cover transition group-hover:scale-[1.02]"
                                        />
                                    </div>
                                    <div class="flex flex-1 flex-col gap-1 p-4">
                                        <div class="flex items-baseline justify-between gap-2">
                                            <p class="truncate text-label text-ink">{{ $vendor['name'] }}</p>
                                            <span class="shrink-0 rounded-full border border-line px-1.5 py-0.5 text-[10px] font-semibold uppercase text-muted">
                                                {{ strtoupper($vendor['locale']) }}
                                            </span>
                                        </div>
                                        @if (! empty($vendor['location']))
                                            <p class="truncate text-caption text-muted">{{ $vendor['location'] }}</p>
                                        @endif
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </section>
        @endif

        {{-- =========================== The problem =========================== --}}
        <section class="pt-24 md:pt-32">
            <div class="mb-10 flex flex-col gap-3">
                <span class="text-caption uppercase tracking-wide text-muted">The problem</span>
                <h2 class="max-w-2xl text-section text-ink">
                    Three things keep micro-businesses invisible to the people walking right past them.
                </h2>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                {{-- Each column: a small solution preview on top, the problem card below. --}}

                {{-- Visibility → a published feed at a real URL --}}
                <div class="flex flex-col gap-3">
                    <div class="flex h-32 flex-col justify-between rounded-lg border border-line bg-surface p-4">
                        <span class="text-[10px] font-semibold uppercase tracking-wider text-muted">FeedAI gives them</span>
                        <div class="flex items-center justify-center">
                            <span class="inline-flex items-center gap-2 rounded-full border border-line bg-canvas px-3 py-1.5 shadow-sm">
                                <span class="relative flex h-1.5 w-1.5">
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-live opacity-70"></span>
                                    <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-live"></span>
                                </span>
                                <span class="font-mono text-[12px] text-ink">feedai/</span><span class="font-mono text-[12px] text-muted">mae-som</span>
                            </span>
                        </div>
                        <span class="text-caption text-muted">A live, shareable feed at a real URL.</span>
                    </div>
                    <div class="rounded-lg border border-line bg-canvas p-6">
                        <p class="text-caption uppercase tracking-wide text-muted">Digital visibility</p>
                        <h3 class="mt-3 text-title text-ink">No website, no marketing.</h3>
                        <p class="mt-3 text-body text-muted">
                            Phenomenal products and zero web presence.
                            Building a site is too technical, too slow, too expensive.
                        </p>
                    </div>
                </div>

                {{-- Language → the in-feed locale switcher --}}
                <div class="flex flex-col gap-3">
                    <div class="flex h-32 flex-col justify-between rounded-lg border border-line bg-surface p-4">
                        <span class="text-[10px] font-semibold uppercase tracking-wider text-muted">FeedAI gives them</span>
                        <div class="flex items-center justify-center">
                            <div class="inline-flex items-center gap-0.5 rounded-full border border-line bg-canvas p-0.5">
                                <span class="rounded-full px-3 py-1 text-[11px] font-semibold text-muted">EN</span>
                                <span class="rounded-full bg-ink px-3 py-1 text-[11px] font-semibold text-canvas">DE</span>
                                <span class="rounded-full px-3 py-1 text-[11px] font-semibold text-muted">TH</span>
                            </div>
                        </div>
                        <span class="text-caption text-muted">Every block, round-tripped in three languages.</span>
                    </div>
                    <div class="rounded-lg border border-line bg-canvas p-6">
                        <p class="text-caption uppercase tracking-wide text-muted">Language barrier</p>
                        <h3 class="mt-3 text-title text-ink">Vendor speaks Thai. Tourist reads German.</h3>
                        <p class="mt-3 text-body text-muted">
                            Even with a page, the language gap blocks the conversation
                            that converts curiosity into a sale.
                        </p>
                    </div>
                </div>

                {{-- Payment → three direct rails --}}
                <div class="flex flex-col gap-3">
                    <div class="flex h-32 flex-col justify-between rounded-lg border border-line bg-surface p-4">
                        <span class="text-[10px] font-semibold uppercase tracking-wider text-muted">FeedAI gives them</span>
                        <div class="flex flex-wrap items-center justify-center gap-1.5">
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-line bg-canvas px-2.5 py-1 text-[11px] font-semibold text-ink">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="size-3" aria-hidden="true">
                                    <rect x="4" y="4" width="6" height="6" rx="1" stroke="currentColor" stroke-width="1.6"/>
                                    <rect x="14" y="4" width="6" height="6" rx="1" stroke="currentColor" stroke-width="1.6"/>
                                    <rect x="4" y="14" width="6" height="6" rx="1" stroke="currentColor" stroke-width="1.6"/>
                                    <path d="M14 14h3v3M20 17v3M14 20h3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                </svg>
                                PromptPay
                            </span>
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-line bg-canvas px-2.5 py-1 text-[11px] font-semibold text-ink">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="size-3" aria-hidden="true">
                                    <rect x="3" y="6" width="18" height="13" rx="2" stroke="currentColor" stroke-width="1.6"/>
                                    <path d="M3 10h18M7 15h3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                </svg>
                                Card
                            </span>
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-line bg-canvas px-2.5 py-1 text-[11px] font-semibold text-ink">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="size-3" aria-hidden="true">
                                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6"/>
                                    <path d="M9 10l3-3 3 3M9 14l3 3 3-3M12 7v10" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Crypto
                            </span>
                        </div>
                        <span class="text-caption text-muted">Direct payments. FeedAI never holds the money.</span>
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
                    Same building blocks, different vendor, every feed feels custom because the AI
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
                        <p class="text-body text-muted">Service list, opening hours, testimonials, a contact form for appointment requests, translated both ways automatically.</p>
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
                {{-- Step 01 — onboarding chat snippet --}}
                <li class="flex flex-col gap-3">
                    <div class="flex h-32 flex-col justify-center gap-1.5 rounded-lg border border-line bg-surface p-4">
                        <div class="flex items-start gap-2">
                            <span aria-hidden="true" class="flex size-5 shrink-0 items-center justify-center rounded-full bg-ink text-canvas">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="size-2.5">
                                    <path d="M12 3v3M12 18v3M3 12h3M18 12h3M5.6 5.6l2.1 2.1M16.3 16.3l2.1 2.1M5.6 18.4l2.1-2.1M16.3 7.7l2.1-2.1" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <span class="rounded-lg rounded-tl-sm border border-line bg-canvas px-2.5 py-1.5 text-[11px] text-ink">
                                What does your stall serve?
                            </span>
                        </div>
                        <div class="flex items-start justify-end">
                            <span class="rounded-lg rounded-tr-sm bg-ink px-2.5 py-1.5 text-[11px] text-canvas">
                                ผัดไทย กุ้งสด ไก่ ทูน่า
                            </span>
                        </div>
                    </div>
                    <div class="rounded-lg border border-line bg-canvas p-6">
                        <span class="text-caption uppercase tracking-wide text-muted">Step 01</span>
                        <h3 class="mt-3 text-title text-ink">Chat with the AI</h3>
                        <p class="mt-3 text-body text-muted">
                            Six quick questions in the vendor's language. They answer in their own words. Photos optional.
                        </p>
                    </div>
                </li>

                {{-- Step 02 — mini assembled feed --}}
                <li class="flex flex-col gap-3">
                    <div class="flex h-32 items-center justify-center rounded-lg border border-line bg-surface p-4">
                        <div class="flex w-14 flex-col rounded-xl border-2 border-ink bg-canvas p-1 shadow-[0_6px_14px_rgba(15,23,42,0.12)]" aria-hidden="true">
                            <div class="flex flex-col gap-1 overflow-hidden rounded-md">
                                <div class="h-7 w-full rounded-sm bg-ink/85"></div>
                                <div class="h-1 w-full rounded-full bg-ink/30"></div>
                                <div class="h-1 w-2/3 rounded-full bg-ink/30"></div>
                                <div class="mt-0.5 h-3 w-full rounded-sm bg-ink/15"></div>
                                <div class="h-1.5 w-full rounded-full bg-ink"></div>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg border border-line bg-canvas p-6">
                        <span class="text-caption uppercase tracking-wide text-muted">Step 02</span>
                        <h3 class="mt-3 text-title text-ink">A feed appears</h3>
                        <p class="mt-3 text-body text-muted">
                            Hero, about, menu, hours, contact, CTA, pay button, contact form, assembled live at
                            <code class="font-mono text-caption">feedai/{slug}</code>.
                        </p>
                    </div>
                </li>

                {{-- Step 03 — translation flow --}}
                <li class="flex flex-col gap-3">
                    <div class="flex h-32 items-center justify-center rounded-lg border border-line bg-surface p-4">
                        <div class="flex items-center gap-1.5">
                            <span class="rounded-full bg-ink px-2.5 py-1 text-[11px] font-semibold text-canvas">TH</span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="size-3.5 text-muted" aria-hidden="true">
                                <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <div class="flex flex-col gap-1">
                                <span class="rounded-full border border-line bg-canvas px-2.5 py-0.5 text-[11px] font-semibold text-ink">EN</span>
                                <span class="rounded-full border border-line bg-canvas px-2.5 py-0.5 text-[11px] font-semibold text-ink">DE</span>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg border border-line bg-canvas p-6">
                        <span class="text-caption uppercase tracking-wide text-muted">Step 03</span>
                        <h3 class="mt-3 text-title text-ink">Auto-translated</h3>
                        <p class="mt-3 text-body text-muted">
                            Every block is translated into the other two languages on save. Tourists browse in theirs without the vendor lifting a finger.
                        </p>
                    </div>
                </li>

                {{-- Step 04 — the public bottom-nav (Home · Pay · Message) --}}
                <li class="flex flex-col gap-3">
                    <div class="flex h-32 items-center justify-center rounded-lg border border-line bg-surface p-4">
                        <div class="inline-flex items-center gap-1.5 rounded-full border border-line bg-canvas px-2 py-2 shadow-[0_6px_14px_rgba(15,23,42,0.08)]">
                            <span class="flex size-8 items-center justify-center rounded-full text-muted" aria-label="Home">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="size-4">
                                    <path d="M3 11l9-8 9 8M5 10v10h14V10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span class="flex size-8 items-center justify-center rounded-full bg-ink text-canvas" aria-label="Pay">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="size-4">
                                    <rect x="3" y="6" width="18" height="13" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M3 10h18M7 15h3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <span class="flex size-8 items-center justify-center rounded-full text-muted" aria-label="Message">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="size-4">
                                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                        </div>
                    </div>
                    <div class="rounded-lg border border-line bg-canvas p-6">
                        <span class="text-caption uppercase tracking-wide text-muted">Step 04</span>
                        <h3 class="mt-3 text-title text-ink">Chat &amp; pay</h3>
                        <p class="mt-3 text-body text-muted">
                            Tourists message in their own language and pay their preferred way. Both sides see only theirs.
                        </p>
                    </div>
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
                            Meet Mae Som, Pad Thai on Khao San Road since 1987.
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

        {{-- =========================== Translated feed showcase =========================== --}}
        <section class="pt-24 md:pt-32">
            <div class="mb-10 flex max-w-2xl flex-col gap-3">
                <span class="text-caption uppercase tracking-wide text-muted">Bidirectional translation</span>
                <h2 class="text-section text-ink">
                    One feed. Saved once. Read in three languages.
                </h2>
                <p class="text-body text-muted">
                    The vendor writes in their own language — Thai, here. Every component is translated
                    into the other two locales the moment it's saved. Same content. Different reader.
                </p>
            </div>

            <div class="grid items-start gap-6 md:grid-cols-[1fr_auto_1fr] md:gap-10">
                {{-- Phone 1 — DE --}}
                <figure class="flex flex-col items-center gap-4">
                    <div class="relative w-full max-w-[280px]">
                        <div class="absolute -inset-6 rounded-[42px] bg-gradient-to-br from-surface to-canvas blur-2xl opacity-60" aria-hidden="true"></div>
                        <div class="relative rounded-[36px] border-4 border-ink bg-canvas p-2 shadow-[0_24px_48px_rgba(15,23,42,0.18)]">
                            <div class="overflow-hidden rounded-[28px] bg-canvas">
                                <img
                                    src="/img/pitch/vendor-de-mobile.png"
                                    alt="Pranee Thai Massage feed shown in German"
                                    loading="lazy"
                                    class="block w-full"
                                />
                            </div>
                        </div>
                    </div>
                    <figcaption class="flex items-center gap-2 text-caption uppercase tracking-wide text-muted">
                        <span class="rounded-full border border-line bg-canvas px-2 py-0.5 font-mono text-[10px] tracking-wide text-ink">DE</span>
                        German tourist
                    </figcaption>
                </figure>

                {{-- Connector / translation indicator (desktop only) --}}
                <div aria-hidden="true" class="hidden flex-col items-center justify-center gap-2 self-center md:flex">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-line bg-canvas text-ink">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="size-4">
                            <path d="M3 7h10M8 5v2M5 11l3 6 3-6M14 17l4-10 4 10M15 14h6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span class="font-mono text-caption text-muted">th → de · en</span>
                </div>

                {{-- Phone 2 — TH --}}
                <figure class="flex flex-col items-center gap-4">
                    <div class="relative w-full max-w-[280px]">
                        <div class="absolute -inset-6 rounded-[42px] bg-gradient-to-br from-surface to-canvas blur-2xl opacity-60" aria-hidden="true"></div>
                        <div class="relative rounded-[36px] border-4 border-ink bg-canvas p-2 shadow-[0_24px_48px_rgba(15,23,42,0.18)]">
                            <div class="overflow-hidden rounded-[28px] bg-canvas">
                                <img
                                    src="/img/pitch/vendor-th-mobile.png"
                                    alt="Pranee Thai Massage feed shown in Thai — the vendor's source language"
                                    loading="lazy"
                                    class="block w-full"
                                />
                            </div>
                        </div>
                    </div>
                    <figcaption class="flex items-center gap-2 text-caption uppercase tracking-wide text-muted">
                        <span class="rounded-full border border-line bg-canvas px-2 py-0.5 font-mono text-[10px] tracking-wide text-ink">TH</span>
                        Source language · vendor
                    </figcaption>
                </figure>
            </div>

            <p class="mt-10 text-center text-caption text-muted">
                Resolved per request: <span class="font-mono text-ink">?lang=de</span> · cookie · <span class="font-mono">Accept-Language</span> · vendor source · fallback <span class="font-mono">en</span>.
            </p>
        </section>

        {{-- =========================== Chat Bridge =========================== --}}
        <section class="grid items-center gap-10 pt-24 md:grid-cols-[1fr_0.85fr] md:gap-14 md:pt-32">
            <div class="flex flex-col gap-4">
                <span class="text-caption uppercase tracking-wide text-muted">Killer feature</span>
                <h2 class="text-section text-ink">The Contact Bridge.</h2>
                <p class="text-body text-muted">
                    A tourist writes in their own language — German, here. The vendor reads it in Thai
                    and replies in Thai. The tourist sees a clean German answer minutes later.
                    Neither side ever sees the other's language.
                </p>
                <p class="text-body text-muted">
                    The banner above the form reads <span class="font-mono text-ink">"You write in :you · :name reads in :them"</span>,
                    so both sides know exactly which way the translation goes. Email notifications nudge
                    them when there's something new.
                </p>
                <ul class="mt-2 grid gap-2 text-body text-muted">
                    <li class="flex items-start gap-2">
                        <span class="mt-2 inline-block h-1.5 w-1.5 shrink-0 rounded-full bg-ink"></span>
                        Anonymous, tokenised conversations — no login for the tourist.
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="mt-2 inline-block h-1.5 w-1.5 shrink-0 rounded-full bg-ink"></span>
                        Threads roll up into the vendor's dashboard inbox.
                    </li>
                </ul>
            </div>

            {{-- Real screenshot of the Contact Bridge UI in a phone frame --}}
            <div class="relative mx-auto w-full max-w-[300px]">
                <div class="absolute -inset-6 rounded-[42px] bg-gradient-to-br from-surface to-canvas blur-2xl opacity-60" aria-hidden="true"></div>
                <div class="relative rounded-[36px] border-4 border-ink bg-canvas p-2 shadow-[0_24px_48px_rgba(15,23,42,0.18)]">
                    <div class="overflow-hidden rounded-[28px] bg-canvas">
                        <img
                            src="/img/pitch/contact-bridge-mobile.png"
                            alt="Contact Bridge: a German tourist writes to Khao San Coffee Lab, the vendor reads in English."
                            loading="lazy"
                            class="block w-full"
                        />
                    </div>
                </div>
            </div>
        </section>

        {{-- =========================== Payment philosophy =========================== --}}
        <section class="pt-24 md:pt-32">
            <div class="mb-10 grid items-center gap-10 md:grid-cols-[1fr_0.7fr] md:gap-14">
                <div class="flex flex-col gap-3">
                    <span class="text-caption uppercase tracking-wide text-muted">Payment philosophy</span>
                    <h2 class="text-section text-ink">
                        FeedAI never holds your money.
                    </h2>
                    <p class="text-body text-muted">
                        We're a payment-options aggregator, not a payment processor. Direct methods stay direct,
                        and even card payments are routed straight to the vendor's own Stripe account.
                    </p>
                    <p class="text-body text-muted">
                        The tourist picks the method that suits them — Card, PromptPay, or Crypto —
                        and the money lands in the vendor's account, not ours.
                    </p>
                </div>

                {{-- Real screenshot of the Pay page in a phone frame --}}
                <div class="relative mx-auto w-full max-w-[270px]">
                    <div class="absolute -inset-6 rounded-[42px] bg-gradient-to-br from-surface to-canvas blur-2xl opacity-60" aria-hidden="true"></div>
                    <div class="relative rounded-[36px] border-4 border-ink bg-canvas p-2 shadow-[0_24px_48px_rgba(15,23,42,0.18)]">
                        <div class="overflow-hidden rounded-[28px] bg-canvas">
                            <img
                                src="/img/pitch/pay-page-mobile.png"
                                alt="Pay page: tourist picks between PromptPay QR and Crypto wallet for Khao San Coffee Lab."
                                loading="lazy"
                                class="block w-full"
                            />
                        </div>
                    </div>
                </div>
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

        {{-- =========================== Editor (vendor backend) =========================== --}}
        <section class="pt-24 md:pt-32">
            <div class="mb-10 grid items-end gap-6 md:grid-cols-[1fr_auto] md:gap-10">
                <div class="flex flex-col gap-3">
                    <span class="text-caption uppercase tracking-wide text-muted">For vendors</span>
                    <h2 class="max-w-xl text-section text-ink">
                        Editing the feed is the same chat.
                    </h2>
                    <p class="max-w-xl text-body text-muted">
                        After onboarding, vendors edit their live feed the same way they built it —
                        by typing into the AI assistant. The preview on the left updates instantly,
                        and any block can be clicked to open a structured form.
                    </p>
                </div>
                <ul class="grid gap-2 text-body text-muted md:text-right">
                    <li class="flex items-start gap-2 md:flex-row-reverse">
                        <span class="mt-2 inline-block h-1.5 w-1.5 shrink-0 rounded-full bg-ink"></span>
                        <span>Natural-language requests</span>
                    </li>
                    <li class="flex items-start gap-2 md:flex-row-reverse">
                        <span class="mt-2 inline-block h-1.5 w-1.5 shrink-0 rounded-full bg-ink"></span>
                        <span>Click-a-block structured forms</span>
                    </li>
                    <li class="flex items-start gap-2 md:flex-row-reverse">
                        <span class="mt-2 inline-block h-1.5 w-1.5 shrink-0 rounded-full bg-ink"></span>
                        <span>Drop a photo, the agent routes it</span>
                    </li>
                </ul>
            </div>

            {{-- Browser/monitor framing for the desktop dashboard screenshot --}}
            <figure class="relative">
                <div aria-hidden="true" class="absolute -inset-x-4 -bottom-6 -top-2 -z-10 rounded-[24px] bg-gradient-to-br from-surface to-canvas blur-2xl opacity-70"></div>
                <div class="overflow-hidden rounded-xl border border-line bg-canvas shadow-[0_24px_48px_rgba(15,23,42,0.08)]">
                    {{-- Window chrome --}}
                    <div class="flex items-center justify-between border-b border-line bg-surface px-4 py-2">
                        <div class="flex items-center gap-1.5" aria-hidden="true">
                            <span class="block size-2.5 rounded-full bg-line"></span>
                            <span class="block size-2.5 rounded-full bg-line"></span>
                            <span class="block size-2.5 rounded-full bg-line"></span>
                        </div>
                        <span class="font-mono text-caption text-muted">feedai · dashboard / feed</span>
                        <span aria-hidden="true" class="w-12"></span>
                    </div>
                    <img
                        src="/img/pitch/dashboard-feed.png"
                        alt="Vendor dashboard with live preview on the left and the edit-by-chat assistant on the right."
                        loading="lazy"
                        class="block w-full"
                    />
                </div>
            </figure>
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
