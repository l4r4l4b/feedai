<?php

it('renders the demo vendor feed', function () {
    $response = $this->get('/demo');

    $response->assertOk()
        ->assertSee('Mae Som Pad Thai', escape: false)
        ->assertSee('Seit 1987 frisch gebraten am Khao San Road', escape: false)
        ->assertSee('Bangkok · Streetfood', escape: false)
        ->assertSee('aspect-[4/5]', escape: false);
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
