<?php

use App\Models\Vendor;
use App\Services\ContentWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('vendors');
});

it('renders skeleton placeholders for all required components when feed is empty and builder=1', function () {
    $vendor = Vendor::factory()->live()->create(['slug' => 'empty-bistro']);
    app(ContentWriter::class)->initializeVendor($vendor);

    $this->get('/empty-bistro?builder=1')
        ->assertOk()
        ->assertSee('Hero · empty', escape: false)
        ->assertSee('About · empty', escape: false)
        ->assertSee('Menu · empty', escape: false)
        ->assertSee('Opening hours · empty', escape: false)
        ->assertSee('Contact · empty', escape: false);
});

it('does not render skeletons when builder param is missing', function () {
    $vendor = Vendor::factory()->live()->create(['slug' => 'empty-bistro']);
    app(ContentWriter::class)->initializeVendor($vendor);

    $this->get('/empty-bistro')
        ->assertOk()
        ->assertDontSee('Hero · empty', escape: false)
        ->assertDontSee('Menu · empty', escape: false);
});

it('only renders skeletons for components that are not yet active', function () {
    $vendor = Vendor::factory()->live()->create(['slug' => 'partial-bistro']);
    $writer = app(ContentWriter::class);
    $writer->initializeVendor($vendor);
    $writer->fillComponent($vendor, 'home', 'hero', ['image' => 'https://x.test/h.jpg', 'title' => 'Test']);
    $writer->fillComponent($vendor, 'home', 'about', [], 'about body');

    $this->get('/partial-bistro?builder=1')
        ->assertOk()
        ->assertDontSee('Hero · empty', escape: false)
        ->assertDontSee('About · empty', escape: false)
        ->assertSee('Menu · empty', escape: false)
        ->assertSee('Opening hours · empty', escape: false)
        ->assertSee('Contact · empty', escape: false);
});
