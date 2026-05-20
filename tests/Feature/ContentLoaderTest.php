<?php

use App\Services\ContentLoader;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('vendors');

    Storage::disk('vendors')->put('acme/vendor.yaml', <<<'YAML'
slug: acme
name: Acme Tours
template: default
locale: th
status: draft
YAML);

    Storage::disk('vendors')->put('acme/pages/home.yaml', <<<'YAML'
slug: home
components:
  - type: hero
    file: 01-hero.md
YAML);

    Storage::disk('vendors')->put('acme/content/home/01-hero.md', <<<'MD'
---
title: Acme Tours
subtitle: Touren durch Bangkok
badge_label: Bangkok
image: https://example.test/hero.jpg
---

Optionaler Body-Text.
MD);
});

it('loads vendor metadata', function () {
    $vendor = app(ContentLoader::class)->loadVendor('acme');

    expect($vendor)
        ->toMatchArray([
            'slug' => 'acme',
            'name' => 'Acme Tours',
            'template' => 'default',
            'locale' => 'th',
            'status' => 'draft',
        ]);
});

it('loads page with component list', function () {
    $page = app(ContentLoader::class)->loadPage('acme', 'home');

    expect($page['slug'])->toBe('home')
        ->and($page['components'])->toHaveCount(1)
        ->and($page['components'][0])->toMatchArray([
            'type' => 'hero',
            'file' => '01-hero.md',
        ]);
});

it('loads component with parsed frontmatter and body', function () {
    $hero = app(ContentLoader::class)->loadComponent('acme', 'home', 'hero', '01-hero.md');

    expect($hero['type'])->toBe('hero')
        ->and($hero['fields']['title'])->toBe('Acme Tours')
        ->and($hero['fields']['subtitle'])->toBe('Touren durch Bangkok')
        ->and($hero['fields']['badgeLabel'])->toBe('Bangkok')
        ->and($hero['fields']['image'])->toBe('https://example.test/hero.jpg')
        ->and($hero['body'])->toBe('Optionaler Body-Text.');
});

it('throws when vendor does not exist', function () {
    app(ContentLoader::class)->loadVendor('does-not-exist');
})->throws(RuntimeException::class, 'Vendor not found');

it('throws when required field is missing', function () {
    Storage::disk('vendors')->put('acme/content/home/01-hero.md', <<<'MD'
---
subtitle: Ohne Titel und Bild
---
MD);

    app(ContentLoader::class)->loadComponent('acme', 'home', 'hero', '01-hero.md');
})->throws(InvalidArgumentException::class, "Missing required field 'image'");
