<?php

use App\Ai\Agents\ImageAnalyzer;
use App\Ai\Tools\ActivateComponent;
use App\Ai\Tools\CreateSubpage;
use App\Ai\Tools\DeactivateComponent;
use App\Ai\Tools\FillComponent;
use App\Ai\Tools\FinalizeOnboarding;
use App\Ai\Tools\InitializeVendorFeed;
use App\Ai\Tools\ReorderComponents;
use App\Ai\Tools\UpdateComponent;
use App\Ai\Tools\UploadImage;
use App\Jobs\TranslateComponent;
use App\Models\OnboardingSession;
use App\Models\Vendor;
use App\Services\ContentLoader;
use App\Services\ContentWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('vendors');
    Storage::fake('public');
    Queue::fake();
});

function makeTool(string $class, Vendor $vendor): object
{
    return app($class, ['vendor' => $vendor]);
}

it('InitializeVendorFeed updates vendor + creates storage skeleton', function () {
    $vendor = Vendor::factory()->create(['slug' => 'acme', 'name' => 'placeholder', 'locale' => 'en']);

    $result = (string) makeTool(InitializeVendorFeed::class, $vendor)->handle(new Request([
        'name' => 'Acme Tours',
        'locale' => 'th',
    ]));

    $vendor->refresh();
    expect($vendor->name)->toBe('Acme Tours')
        ->and($vendor->locale)->toBe('th');

    Storage::disk('vendors')->assertExists('acme/vendor.yaml');
    Storage::disk('vendors')->assertExists('acme/pages/home.yaml');
    expect($result)->toContain('Acme Tours');
});

it('ActivateComponent adds component to page list', function () {
    $vendor = Vendor::factory()->create(['slug' => 'acme']);
    app(ContentWriter::class)->initializeVendor($vendor);

    makeTool(ActivateComponent::class, $vendor)->handle(new Request([
        'page_slug' => 'home',
        'type' => 'hero',
    ]));

    $page = app(ContentLoader::class)->loadPage('acme', 'home');
    expect(array_column($page['components'], 'type'))->toContain('hero');
});

it('FillComponent writes content and dispatches TranslateComponent', function () {
    $vendor = Vendor::factory()->create(['slug' => 'acme']);
    app(ContentWriter::class)->initializeVendor($vendor);

    makeTool(FillComponent::class, $vendor)->handle(new Request([
        'page_slug' => 'home',
        'type' => 'hero',
        'fields' => json_encode([
            'image' => 'https://x.test/hero.jpg',
            'title' => 'Acme',
            'subtitle' => 'Best',
        ]),
    ]));

    $hero = app(ContentLoader::class)->loadComponent('acme', 'home', 'hero', '01-hero.md');
    expect($hero['fields']['title'])->toBe('Acme');

    Queue::assertPushed(TranslateComponent::class, fn (TranslateComponent $job): bool => $job->vendorId === $vendor->id
        && $job->pageSlug === 'home'
        && $job->componentType === 'hero');
});

it('FillComponent rejects invalid JSON', function () {
    $vendor = Vendor::factory()->create(['slug' => 'acme']);
    app(ContentWriter::class)->initializeVendor($vendor);

    makeTool(FillComponent::class, $vendor)->handle(new Request([
        'type' => 'hero',
        'fields' => '{ broken',
    ]));
})->throws(InvalidArgumentException::class, 'fields must be a valid JSON');

it('FillComponent enforces schema required fields', function () {
    $vendor = Vendor::factory()->create(['slug' => 'acme']);
    app(ContentWriter::class)->initializeVendor($vendor);

    makeTool(FillComponent::class, $vendor)->handle(new Request([
        'type' => 'hero',
        'fields' => json_encode(['subtitle' => 'no image, no title']),
    ]));
})->throws(InvalidArgumentException::class, "Missing required field 'image'");

it('UpdateComponent overwrites and dispatches retranslation', function () {
    $vendor = Vendor::factory()->create(['slug' => 'acme']);
    $writer = app(ContentWriter::class);
    $writer->initializeVendor($vendor);
    $writer->fillComponent($vendor, 'home', 'hero', ['image' => 'a', 'title' => 'old']);

    Queue::fake();

    makeTool(UpdateComponent::class, $vendor)->handle(new Request([
        'type' => 'hero',
        'fields' => json_encode(['image' => 'b', 'title' => 'new']),
    ]));

    $hero = app(ContentLoader::class)->loadComponent('acme', 'home', 'hero', '01-hero.md');
    expect($hero['fields']['title'])->toBe('new');

    Queue::assertPushed(TranslateComponent::class);
});

it('DeactivateComponent removes from list but keeps file', function () {
    $vendor = Vendor::factory()->create(['slug' => 'acme']);
    $writer = app(ContentWriter::class);
    $writer->initializeVendor($vendor);
    $writer->fillComponent($vendor, 'home', 'hero', ['image' => 'x', 'title' => 'y']);

    makeTool(DeactivateComponent::class, $vendor)->handle(new Request([
        'type' => 'hero',
    ]));

    $page = app(ContentLoader::class)->loadPage('acme', 'home');
    expect($page['components'])->toBe([]);
    Storage::disk('vendors')->assertExists('acme/content/home/01-hero.md');
});

it('CreateSubpage creates an empty page file', function () {
    $vendor = Vendor::factory()->create(['slug' => 'acme']);
    app(ContentWriter::class)->initializeVendor($vendor);

    makeTool(CreateSubpage::class, $vendor)->handle(new Request([
        'page_slug' => 'tour-floating-market',
    ]));

    Storage::disk('vendors')->assertExists('acme/pages/tour-floating-market.yaml');
});

it('ReorderComponents updates the order', function () {
    $vendor = Vendor::factory()->create(['slug' => 'acme']);
    $writer = app(ContentWriter::class);
    $writer->initializeVendor($vendor);
    $writer->activateComponent($vendor, 'home', 'hero');
    $writer->activateComponent($vendor, 'home', 'about');
    $writer->activateComponent($vendor, 'home', 'menu');

    makeTool(ReorderComponents::class, $vendor)->handle(new Request([
        'types' => ['menu', 'about', 'hero'],
    ]));

    $page = app(ContentLoader::class)->loadPage('acme', 'home');
    expect(array_column($page['components'], 'type'))->toBe(['menu', 'about', 'hero']);
});

it('UploadImage stores via Spatie and returns URL + AI description', function () {
    ImageAnalyzer::fake([
        [
            'description' => 'A vendor portrait',
            'alt_text' => 'Portrait',
            'suggested_intent' => 'hero',
            'tags' => ['portrait'],
            'detected_text' => '',
            'locale_hint' => '',
        ],
    ]);

    $vendor = Vendor::factory()->create(['slug' => 'acme']);

    // 1×1 transparent PNG
    $pngBase64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

    $result = (string) makeTool(UploadImage::class, $vendor)->handle(new Request([
        'data' => $pngBase64,
        'mime_type' => 'image/png',
        'intended_use' => 'hero',
    ]));

    expect($result)->toContain('suggested_intent=hero')
        ->and($result)->toContain('description=A vendor portrait')
        ->and($vendor->refresh()->getMedia('images'))->toHaveCount(1);
});

it('UploadImage rejects unsupported mime type', function () {
    $vendor = Vendor::factory()->create(['slug' => 'acme']);

    makeTool(UploadImage::class, $vendor)->handle(new Request([
        'data' => base64_encode('not-an-image'),
        'mime_type' => 'image/gif',
        'intended_use' => 'hero',
    ]));
})->throws(InvalidArgumentException::class, 'Unsupported mime type');

it('FinalizeOnboarding sets vendor live and finalizes sessions', function () {
    $vendor = Vendor::factory()->create(['slug' => 'acme', 'status' => 'draft']);
    $session = OnboardingSession::factory()->create(['vendor_id' => $vendor->id]);

    $result = (string) makeTool(FinalizeOnboarding::class, $vendor)->handle(new Request([]));

    $vendor->refresh();
    $session->refresh();

    expect($vendor->status)->toBe('live')
        ->and($vendor->onboarding_completed_at)->not->toBeNull()
        ->and($session->status)->toBe('finalized')
        ->and($session->finalized_at)->not->toBeNull()
        ->and($result)->toContain('LIVE');
});

it('all tools resolve via the container with vendor in constructor', function () {
    $vendor = Vendor::factory()->create();

    $tools = [
        InitializeVendorFeed::class,
        ActivateComponent::class,
        FillComponent::class,
        DeactivateComponent::class,
        UpdateComponent::class,
        UploadImage::class,
        CreateSubpage::class,
        ReorderComponents::class,
        FinalizeOnboarding::class,
    ];

    foreach ($tools as $class) {
        $tool = app($class, ['vendor' => $vendor]);
        expect($tool)->toBeInstanceOf(Tool::class)
            ->and($tool->description())->not->toBeEmpty();
    }
});
