<?php

it('renders the demo vendor feed', function () {
    $response = $this->get('/demo?lang=en');

    $response->assertOk()
        ->assertSee('Mae Som Pad Thai', escape: false)
        ->assertSee('Khao San Road · Bangkok', escape: false)
        ->assertSee('4.95 (203)', escape: false)
        ->assertSee('aspect-[3/4]', escape: false);
});

it('returns 404 for unknown vendor', function () {
    $this->get('/does-not-exist')->assertNotFound();
});

it('renders home page explicitly via /demo/home', function () {
    $this->get('/demo/home')->assertOk()->assertSee('Mae Som Pad Thai', escape: false);
});

it('returns 404 for unknown sub-page', function () {
    $this->get('/demo/menu')->assertNotFound();
});
