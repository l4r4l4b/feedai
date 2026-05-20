<?php

use App\Models\Vendor;
use App\Services\ContentLoader;
use App\Services\ContentWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('vendors');
});

it('initializes a new vendor with empty home page', function () {
    $vendor = Vendor::factory()->create(['slug' => 'acme', 'name' => 'Acme Tours']);

    app(ContentWriter::class)->initializeVendor($vendor);

    Storage::disk('vendors')->assertExists('acme/vendor.yaml');
    Storage::disk('vendors')->assertExists('acme/pages/home.yaml');

    $vendorData = app(ContentLoader::class)->loadVendor('acme');
    expect($vendorData['name'])->toBe('Acme Tours');

    $page = app(ContentLoader::class)->loadPage('acme', 'home');
    expect($page['components'])->toBe([]);
});

it('activates a component and creates its content file', function () {
    $vendor = Vendor::factory()->create(['slug' => 'acme']);
    $writer = app(ContentWriter::class);
    $writer->initializeVendor($vendor);

    $file = $writer->activateComponent($vendor, 'home', 'hero');

    expect($file)->toBe('01-hero.md');

    Storage::disk('vendors')->assertExists("acme/content/home/{$file}");

    $page = app(ContentLoader::class)->loadPage('acme', 'home');
    expect($page['components'])->toBe([
        ['type' => 'hero', 'file' => '01-hero.md'],
    ]);
});

it('is idempotent when activating the same component twice', function () {
    $vendor = Vendor::factory()->create(['slug' => 'acme']);
    $writer = app(ContentWriter::class);
    $writer->initializeVendor($vendor);

    $first = $writer->activateComponent($vendor, 'home', 'hero');
    $second = $writer->activateComponent($vendor, 'home', 'hero');

    expect($first)->toBe($second);

    $page = app(ContentLoader::class)->loadPage('acme', 'home');
    expect($page['components'])->toHaveCount(1);
});

it('fills a component with valid fields and persists frontmatter', function () {
    $vendor = Vendor::factory()->create(['slug' => 'acme']);
    $writer = app(ContentWriter::class);
    $writer->initializeVendor($vendor);

    $writer->fillComponent($vendor, 'home', 'hero', [
        'image' => 'https://example.test/img.jpg',
        'title' => 'Acme Tours',
        'subtitle' => 'Best in town',
        'badge_label' => 'Bangkok',
    ]);

    $hero = app(ContentLoader::class)->loadComponent('acme', 'home', 'hero', '01-hero.md');

    expect($hero['fields']['title'])->toBe('Acme Tours')
        ->and($hero['fields']['badgeLabel'])->toBe('Bangkok');
});

it('rejects fill when required field is missing', function () {
    $vendor = Vendor::factory()->create(['slug' => 'acme']);
    app(ContentWriter::class)->initializeVendor($vendor);

    app(ContentWriter::class)->fillComponent($vendor, 'home', 'hero', [
        'subtitle' => 'no image, no title',
    ]);
})->throws(InvalidArgumentException::class, "Missing required field 'image'");

it('rejects fill for unknown component type', function () {
    $vendor = Vendor::factory()->create(['slug' => 'acme']);
    app(ContentWriter::class)->initializeVendor($vendor);

    app(ContentWriter::class)->fillComponent($vendor, 'home', 'unicorn', ['x' => 1]);
})->throws(InvalidArgumentException::class, 'Unknown component type: unicorn');

it('deactivates a component but keeps the content file', function () {
    $vendor = Vendor::factory()->create(['slug' => 'acme']);
    $writer = app(ContentWriter::class);
    $writer->initializeVendor($vendor);
    $writer->fillComponent($vendor, 'home', 'hero', [
        'image' => 'x',
        'title' => 'y',
    ]);

    $writer->deactivateComponent($vendor, 'home', 'hero');

    $page = app(ContentLoader::class)->loadPage('acme', 'home');
    expect($page['components'])->toBe([]);

    Storage::disk('vendors')->assertExists('acme/content/home/01-hero.md');
});

it('reorders components by type', function () {
    $vendor = Vendor::factory()->create(['slug' => 'acme']);
    $writer = app(ContentWriter::class);
    $writer->initializeVendor($vendor);
    $writer->activateComponent($vendor, 'home', 'hero');
    $writer->activateComponent($vendor, 'home', 'about');
    $writer->activateComponent($vendor, 'home', 'menu');

    $writer->reorderComponents($vendor, 'home', ['menu', 'hero', 'about']);

    $page = app(ContentLoader::class)->loadPage('acme', 'home');
    expect(array_column($page['components'], 'type'))
        ->toBe(['menu', 'hero', 'about']);
});

it('creates a sub-page', function () {
    $vendor = Vendor::factory()->create(['slug' => 'acme']);
    $writer = app(ContentWriter::class);
    $writer->initializeVendor($vendor);

    $writer->createSubpage($vendor, 'tour-floating-market');

    Storage::disk('vendors')->assertExists('acme/pages/tour-floating-market.yaml');

    $page = app(ContentLoader::class)->loadPage('acme', 'tour-floating-market');
    expect($page['slug'])->toBe('tour-floating-market')
        ->and($page['components'])->toBe([]);
});

it('rejects creating a sub-page that already exists', function () {
    $vendor = Vendor::factory()->create(['slug' => 'acme']);
    $writer = app(ContentWriter::class);
    $writer->initializeVendor($vendor);

    $writer->createSubpage($vendor, 'tour');
    $writer->createSubpage($vendor, 'tour');
})->throws(InvalidArgumentException::class, 'Sub-page already exists');
