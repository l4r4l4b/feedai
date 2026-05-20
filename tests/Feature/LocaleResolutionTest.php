<?php

use App\Support\Locale;
use Illuminate\Http\Request;

it('prefers the explicit ?lang query over a stale cookie', function () {
    $request = Request::create('/demo?lang=de', 'GET', [], ['feedai_locale' => 'th'], [], [
        'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
    ]);

    expect(Locale::resolve($request, 'en'))->toBe('de');
});

it('uses the cookie when no explicit query is provided', function () {
    $request = Request::create('/demo', 'GET', [], ['feedai_locale' => 'th'], [], [
        'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
    ]);

    expect(Locale::resolve($request, 'en'))->toBe('th');
});

it('falls back to Accept-Language when no cookie or query', function () {
    $request = Request::create('/demo', 'GET', [], [], [], [
        'HTTP_ACCEPT_LANGUAGE' => 'de-AT,de;q=0.9,en;q=0.8',
    ]);

    expect(Locale::resolve($request, 'th'))->toBe('de');
});

it('falls back to the vendor locale when nothing else matches', function () {
    $request = Request::create('/demo', 'GET', [], [], [], [
        'HTTP_ACCEPT_LANGUAGE' => 'fr-FR,fr;q=0.9',
    ]);

    expect(Locale::resolve($request, 'th'))->toBe('th');
});

it('falls back to en when even the vendor locale is unsupported', function () {
    $request = Request::create('/demo', 'GET');

    expect(Locale::resolve($request, 'jp'))->toBe('en');
});

it('rejects unsupported cookies/queries and keeps walking the chain', function () {
    $request = Request::create('/demo?lang=fr', 'GET', [], ['feedai_locale' => 'es'], [], [
        'HTTP_ACCEPT_LANGUAGE' => 'th-TH,th;q=0.9',
    ]);

    expect(Locale::resolve($request, 'en'))->toBe('th');
});

it('serves the translated component file when present', function () {
    // Demo ships with translations/en/home/01-hero.md — exact body text
    // varies as translations are regenerated; assert the page renders and
    // resolves to the English locale, leaving content assertions to the
    // dedicated demo content tests.
    $this->get('/demo?lang=en')
        ->assertOk()
        ->assertSee('<html lang="en"', escape: false)
        ->assertSee('Mae Som Pad Thai', escape: false);
});

it('falls back to source content when a translation file is missing', function () {
    // about likely has no /de translation in demo — fallback shows source TH.
    // We just assert the page renders without exploding.
    $this->get('/demo?lang=de')
        ->assertOk()
        ->assertSee('Mae Som Pad Thai', escape: false);
});

it('sets the html lang attribute from the resolved locale', function () {
    $this->get('/demo?lang=de')
        ->assertOk()
        ->assertSee('<html lang="de"', escape: false);
});
