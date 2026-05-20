<?php

use App\Ai\Agents\ImageAnalyzer;
use App\Ai\Tools\UploadImage;
use App\Jobs\AnalyzeImage;
use App\Models\Vendor;
use App\Services\VendorImageIngestor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Tools\Request;

uses(RefreshDatabase::class);

function fakeImageAnalyzer(string $intent = 'hero'): void
{
    ImageAnalyzer::fake([
        [
            'description' => 'Wok-Aktion am Streetfood-Stand bei Nacht',
            'alt_text' => 'Vendor kocht am Wok',
            'suggested_intent' => $intent,
            'tags' => ['streetfood', 'wok', 'night'],
            'detected_text' => '',
            'locale_hint' => '',
        ],
    ]);
}

beforeEach(function () {
    Storage::fake('public');
    Storage::fake('vendors');
});

it('ingests an uploaded file and runs analysis synchronously', function () {
    fakeImageAnalyzer();
    $vendor = Vendor::factory()->create();

    $upload = UploadedFile::fake()->image('shot.png', 200, 200);

    $media = app(VendorImageIngestor::class)
        ->fromUpload($vendor, $upload, intent: 'hero', source: 'test', analyzeSync: true);

    expect($media->id)->toBeInt()
        ->and($media->getCustomProperty('source'))->toBe('test')
        ->and($media->getCustomProperty('intent'))->toBe('hero')
        ->and($media->getCustomProperty('description'))->toContain('Wok')
        ->and($media->getCustomProperty('suggested_intent'))->toBe('hero')
        ->and($media->getCustomProperty('tags'))->toContain('streetfood')
        ->and($media->file_name)->toContain('hero-')
        ->and($media->file_name)->toEndWith('.png');
});

it('ingests from base64 and produces matching media', function () {
    fakeImageAnalyzer('menu_item');
    $vendor = Vendor::factory()->create();

    $pngBase64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

    $media = app(VendorImageIngestor::class)
        ->fromBase64($vendor, $pngBase64, 'image/png', intent: 'menu', source: 'agent-tool');

    expect($media->getCustomProperty('source'))->toBe('agent-tool')
        ->and($media->getCustomProperty('suggested_intent'))->toBe('menu_item')
        ->and($media->file_name)->toContain('menu_item-');
});

it('rejects unsupported mime types', function () {
    $vendor = Vendor::factory()->create();
    app(VendorImageIngestor::class)
        ->fromBase64($vendor, base64_encode('not an image'), 'image/gif');
})->throws(InvalidArgumentException::class, 'Unsupported mime type');

it('queues analysis when analyzeSync is false', function () {
    Queue::fake();
    $vendor = Vendor::factory()->create();

    $upload = UploadedFile::fake()->image('async.png', 50, 50);

    $media = app(VendorImageIngestor::class)
        ->fromUpload($vendor, $upload, intent: 'gallery', analyzeSync: false);

    expect($media->getCustomProperty('description'))->toBeIn([null, '']);

    Queue::assertPushed(AnalyzeImage::class, fn (AnalyzeImage $job): bool => $job->mediaId === $media->id);
});

it('writes graceful error metadata if analyzer fails', function () {
    ImageAnalyzer::fake(function () {
        throw new RuntimeException('vision provider down');
    });

    $vendor = Vendor::factory()->create();
    $upload = UploadedFile::fake()->image('boom.png', 80, 80);

    $media = app(VendorImageIngestor::class)
        ->fromUpload($vendor, $upload, intent: 'service', analyzeSync: true);

    expect($media->getCustomProperty('analyzed_at'))->not->toBeNull()
        ->and($media->getCustomProperty('analysis_error'))->toContain('vision provider down')
        ->and($media->getCustomProperty('description'))->toBeIn([null, '']);
});

it('routes UploadImage tool through the same ingestor', function () {
    fakeImageAnalyzer('gallery');
    $vendor = Vendor::factory()->create();

    /** @var UploadImage $tool */
    $tool = app(UploadImage::class, ['vendor' => $vendor]);

    $pngBase64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

    $result = (string) $tool->handle(new Request([
        'data' => $pngBase64,
        'mime_type' => 'image/png',
        'intended_use' => 'gallery',
    ]));

    expect($result)->toContain('suggested_intent=gallery')
        ->toContain('description=Wok-Aktion');

    expect($vendor->fresh()->getMedia('images'))->toHaveCount(1);
});
