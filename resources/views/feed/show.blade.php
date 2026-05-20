<x-layouts::public :vendor="$vendor">
    <div class="flex flex-col gap-7">
        @foreach ($components as $component)
            @php($fields = $component['fields'])
            @php($body = $component['body'] ?? '')

            @switch($component['type'])
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
        @endforeach
    </div>
</x-layouts::public>
