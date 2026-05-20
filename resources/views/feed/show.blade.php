@php
    $builder = (bool) request('builder');
    $requiredTypes = ['hero', 'about', 'menu', 'opening_hours', 'contact_buttons'];
    $activeTypes = array_column($components, 'type');
    $missingRequired = $builder ? array_values(array_diff($requiredTypes, $activeTypes)) : [];
@endphp

<x-layouts::public :vendor="$vendor">
    <div class="flex flex-col gap-7">
        {{-- Demo-mode payment receipt — shown on return from the simulated
             Stripe checkout (?demo_paid=AMOUNT_CENTS in the URL). Real Stripe
             success flows use the session_id flow handled elsewhere. --}}
        @if (request('demo_paid'))
            <div class="flex items-start gap-3 rounded-lg border border-line bg-surface px-4 py-3">
                <span class="text-2xl" aria-hidden="true">✓</span>
                <div class="flex-1">
                    <p class="text-label text-text">Payment received</p>
                    <p class="text-caption text-muted">
                        {{ number_format(((int) request('demo_paid')) / 100, 0) }} THB to {{ $vendor['name'] ?? 'the vendor' }} —
                        demo mode, no real card was charged.
                    </p>
                </div>
            </div>
        @endif

        @foreach ($components as $comp)
            @php($fields = $comp['fields'])
            @php($body = $comp['body'] ?? '')

            <div @class(['relative' => $builder])>
            @if ($builder)
                <x-feed.edit-marker :type="$comp['type']" />
            @endif
            @switch($comp['type'])
                @case('hero')
                    <x-feed.hero
                        :image="$fields['image']"
                        :title="$fields['title']"
                        :location="$fields['location'] ?? null"
                        :rating="$fields['rating'] ?? null"
                    />
                @break

                @case('about')
                    <x-feed.about
                        :section-label="$fields['sectionLabel'] ?? null"
                        :body="$body"
                    />
                @break

                @case('service')
                    <x-feed.service
                        :image="$fields['image']"
                        :title="$fields['title']"
                        :meta="$fields['meta'] ?? null"
                        :rating="$fields['rating'] ?? null"
                        :price="$fields['price']"
                        :cta-url="$fields['ctaUrl'] ?? '#'"
                    />
                @break

                @case('menu')
                    <x-feed.menu
                        :section-label="$fields['sectionLabel'] ?? null"
                        :items="$fields['items'] ?? []"
                    />
                @break

                @case('contact_buttons')
                    <x-feed.contact-buttons
                        :section-label="$fields['sectionLabel'] ?? null"
                        :buttons="$fields['buttons'] ?? []"
                    />
                @break

                @case('opening_hours')
                    <x-feed.opening-hours
                        :section-label="$fields['sectionLabel'] ?? null"
                        :hours="$fields['hours'] ?? []"
                        :note="$fields['note'] ?? null"
                    />
                @break

                @case('gallery')
                    <x-feed.gallery
                        :section-label="$fields['sectionLabel'] ?? null"
                        :ratio="$fields['ratio'] ?? '1:1'"
                        :images="$fields['images'] ?? []"
                    />
                @break

                @case('location')
                    <x-feed.location
                        :section-label="$fields['sectionLabel'] ?? null"
                        :address="$fields['address']"
                        :map-url="$fields['mapUrl'] ?? null"
                        :embed-url="$fields['embedUrl'] ?? null"
                    />
                @break

                @case('faq')
                    <x-feed.faq
                        :section-label="$fields['sectionLabel'] ?? null"
                        :items="$fields['items'] ?? []"
                    />
                @break

                @case('testimonial')
                    <x-feed.testimonial
                        :quote="$fields['quote']"
                        :author="$fields['author']"
                        :role="$fields['role'] ?? null"
                    />
                @break

                @case('cta')
                    <x-feed.cta
                        :title="$fields['title']"
                        :body="$fields['body'] ?? null"
                        :button-label="$fields['buttonLabel']"
                        :button-url="$fields['buttonUrl']"
                    />
                @break

                @case('image_divider')
                    <x-feed.image-divider
                        :image="$fields['image']"
                        :headline="$fields['headline'] ?? null"
                        :sub="$fields['sub'] ?? null"
                    />
                @break

                @case('image_with_text')
                    <x-feed.image-with-text
                        :image="$fields['image']"
                        :headline="$fields['headline'] ?? null"
                        :body="$body"
                    />
                @break

                @case('text_block')
                    <x-feed.text-block
                        :headline="$fields['headline'] ?? null"
                        :body="$body"
                    />
                @break

                @case('highlight_card')
                    <x-feed.highlight-card
                        :icon="$fields['icon']"
                        :headline="$fields['headline']"
                        :body="$fields['body'] ?? null"
                    />
                @break

                @case('contact_form')
                    <x-feed.contact-form
                        :vendor-slug="$vendor['slug']"
                        :section-label="$fields['sectionLabel'] ?? null"
                        :title="$fields['title'] ?? 'Kontakt'"
                        :intro="$fields['intro'] ?? null"
                        :submit-label="$fields['submitLabel'] ?? 'Senden'"
                        :locale="$vendor['locale'] ?? null"
                    />
                @break

                @case('pay_now_trigger')
                    <x-feed.pay-now-trigger
                        :label="$fields['label'] ?? 'BEZAHLEN'"
                        :title="$fields['title']"
                        :url="$fields['url'] ?? '#pay'"
                    />
                @break
            @endswitch
            </div>
        @endforeach

        {{-- Builder mode — render skeletons for required components that
             aren't active yet. They click-postMessage the parent (dashboard
             or onboarding) so a form drawer can open. --}}
        @foreach ($missingRequired as $type)
            @switch($type)
                @case('hero')
                    <x-feed.hero-skeleton type="hero" />
                @break
                @case('about')
                    <x-feed.about-skeleton type="about" />
                @break
                @case('menu')
                    <x-feed.menu-skeleton type="menu" />
                @break
                @case('opening_hours')
                    <x-feed.opening-hours-skeleton type="opening_hours" />
                @break
                @case('contact_buttons')
                    <x-feed.contact-buttons-skeleton type="contact_buttons" />
                @break
            @endswitch
        @endforeach
    </div>
</x-layouts::public>
