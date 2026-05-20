<?php

it('renders all 17 components from the demo vendor without errors', function () {
    // Force source locale so we assert against the canonical (th-source)
    // content; locale-aware translations are covered separately.
    $response = $this->get('/demo?lang=th');

    $response->assertOk()
        // Hero — v2 with location + rating instead of badge_label
        ->assertSee('Mae Som Pad Thai', escape: false)
        ->assertSee('Khao San Road · Bangkok', escape: false)
        ->assertSee('4.95 (203)', escape: false)
        // About
        ->assertSee('ABOUT MAE SOM', escape: false)
        // Image Divider
        ->assertSee('Khao San Road after sunset', escape: false)
        // Service — apostrophe is HTML-escaped, assert on a unique substring without it
        ->assertSee('Cooking Class', escape: false)
        ->assertSee('1,200 THB', escape: false)
        // Menu
        ->assertSee('Pad Thai Goong', escape: false)
        ->assertSee('80 THB', escape: false)
        // Gallery
        ->assertSee('picsum.photos/seed/wok-1', escape: false)
        // Opening Hours
        ->assertSee('Mon–Fri', escape: false)
        ->assertSee('17:00–23:00', escape: false)
        // Location
        ->assertSee('Khao San Road, opposite 7-Eleven', escape: false)
        ->assertSee('Open in Maps', escape: false)
        // Contact Buttons
        ->assertSee('wa.me/66812345678', escape: false)
        ->assertSee('line.me/ti/p/~maesom', escape: false)
        // Highlight Card
        ->assertSee('Cash only', escape: false)
        // Testimonial
        ->assertSee('Lena &amp; Tom', escape: false)
        // FAQ
        ->assertSee('vegetarian options', escape: false)
        // Image with Text
        ->assertSee('Why our Pad Thai tastes different', escape: false)
        // Text Block
        ->assertSee('One small ask', escape: false)
        // CTA
        ->assertSee('Want to learn to cook it yourself', escape: false)
        ->assertSee('Request the class', escape: false)
        // Pay Now
        ->assertSee('Pay right here at the stall', escape: false)
        ->assertSee('/demo/pay', escape: false)
        // Contact Form
        ->assertSee('Message Mae Som directly', escape: false)
        ->assertSee('Send message', escape: false);
});
