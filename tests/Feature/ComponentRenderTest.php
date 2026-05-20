<?php

it('renders all 17 components from the demo vendor without errors', function () {
    $response = $this->get('/demo');

    $response->assertOk()
        // Hero
        ->assertSee('Mae Som Pad Thai', escape: false)
        ->assertSee('Bangkok · Streetfood', escape: false)
        // About
        ->assertSee('ÜBER MAE SOM', escape: false)
        // Image Divider
        ->assertSee('Khao San Road nach Sonnenuntergang', escape: false)
        // Service
        ->assertSee('Mae Soms Kochkurs', escape: false)
        ->assertSee('1.200 THB', escape: false)
        // Menu
        ->assertSee('Pad Thai Goong', escape: false)
        ->assertSee('80 THB', escape: false)
        // Gallery (no text content — just src URLs)
        ->assertSee('picsum.photos/seed/wok-1', escape: false)
        // Opening Hours
        ->assertSee('Mo–Fr', escape: false)
        ->assertSee('17:00–23:00', escape: false)
        // Location
        ->assertSee('Khao San Road, gegenüber 7-Eleven', escape: false)
        ->assertSee('In Maps öffnen', escape: false)
        // Contact Buttons
        ->assertSee('wa.me/66812345678', escape: false)
        ->assertSee('line.me/ti/p/~maesom', escape: false)
        // Highlight Card
        ->assertSee('Cash only', escape: false)
        // Testimonial
        ->assertSee('Lena &amp; Tom', escape: false)
        // FAQ
        ->assertSee('vegetarische Optionen', escape: false)
        // Image with Text
        ->assertSee('Warum unser Pad Thai anders schmeckt', escape: false)
        // Text Block
        ->assertSee('Eine Bitte', escape: false)
        // CTA
        ->assertSee('Lust selbst zu kochen', escape: false)
        ->assertSee('Kurs anfragen', escape: false)
        // Pay Now
        ->assertSee('Direkt vor Ort bezahlen', escape: false)
        ->assertSee('/demo/pay', escape: false)
        // Contact Form
        ->assertSee('Direkt an Mae Som', escape: false)
        ->assertSee('Nachricht senden', escape: false);
});
