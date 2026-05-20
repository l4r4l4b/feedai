<?php

use App\Ai\Agents\Translator;
use App\Jobs\TranslateComponent;
use App\Models\Vendor;
use App\Services\ContentLoader;
use Illuminate\Support\Facades\Storage;

function seedDemoFileFor(Vendor $vendor): string
{
    Storage::disk('vendors')->put("{$vendor->slug}/pages/home.yaml", <<<'YAML'
slug: home
components:
  - type: hero
    file: 01-hero.md
YAML);

    $content = <<<'MD'
---
title: Mae Som Pad Thai
subtitle: Seit 1987 frisch gebraten am Khao San Road
badge_label: Bangkok · Streetfood
image: https://images.unsplash.com/photo-1559314809-0d155014e29e?w=1200&q=80
---

Mae Som steht jeden Abend mit ihrem Wok am selben Eck.
MD;

    Storage::disk('vendors')->put("{$vendor->slug}/content/home/01-hero.md", $content);

    return $content;
}

beforeEach(function () {
    Storage::fake('vendors');
});

it('writes translated files for every target locale except the source', function () {
    $vendor = Vendor::factory()->create(['slug' => 'mae-som', 'locale' => 'th']);
    seedDemoFileFor($vendor);

    Translator::fake([
        '--- TRANSLATED EN ---',
        '--- TRANSLATED DE ---',
    ]);

    (new TranslateComponent($vendor->id, 'home', 'hero'))->handle(app(ContentLoader::class));

    Storage::disk('vendors')->assertExists("{$vendor->slug}/translations/en/home/01-hero.md");
    Storage::disk('vendors')->assertExists("{$vendor->slug}/translations/de/home/01-hero.md");
    Storage::disk('vendors')->assertMissing("{$vendor->slug}/translations/th/home/01-hero.md");

    expect(Storage::disk('vendors')->get("{$vendor->slug}/translations/en/home/01-hero.md"))
        ->toBe('--- TRANSLATED EN ---')
        ->and(Storage::disk('vendors')->get("{$vendor->slug}/translations/de/home/01-hero.md"))
        ->toBe('--- TRANSLATED DE ---');
});

it('passes the raw markdown content to the translator agent', function () {
    $vendor = Vendor::factory()->create(['slug' => 'mae-som', 'locale' => 'th']);
    $content = seedDemoFileFor($vendor);

    Translator::fake()->preventStrayPrompts();
    Translator::fake(fn ($prompt) => "translated:\n{$prompt->prompt}");

    (new TranslateComponent($vendor->id, 'home', 'hero'))->handle(app(ContentLoader::class));

    Translator::assertPrompted(fn ($prompt) => str_contains($prompt->prompt, 'Mae Som Pad Thai'));
});

it('skips source locale when it equals the target', function () {
    $vendor = Vendor::factory()->create(['slug' => 'mae-som', 'locale' => 'en']);
    seedDemoFileFor($vendor);

    Translator::fake(['--- TRANSLATED DE ---']);

    (new TranslateComponent($vendor->id, 'home', 'hero'))->handle(app(ContentLoader::class));

    Storage::disk('vendors')->assertMissing("{$vendor->slug}/translations/en/home/01-hero.md");
    Storage::disk('vendors')->assertExists("{$vendor->slug}/translations/de/home/01-hero.md");
});

it('is a no-op when the vendor does not exist', function () {
    Translator::fake();

    (new TranslateComponent(99999, 'home', 'hero'))->handle(app(ContentLoader::class));

    Translator::assertNeverPrompted();
});

it('is a no-op when the component is not on the page', function () {
    $vendor = Vendor::factory()->create(['slug' => 'mae-som', 'locale' => 'th']);
    seedDemoFileFor($vendor);

    Translator::fake();

    (new TranslateComponent($vendor->id, 'home', 'menu'))->handle(app(ContentLoader::class));

    Translator::assertNeverPrompted();
});

it('exposes a stable unique key per vendor + page + component', function () {
    $job = new TranslateComponent(7, 'home', 'hero');

    expect($job->uniqueId())->toBe('translate:7:home:hero')
        ->and($job->uniqueFor())->toBe(60);
});
