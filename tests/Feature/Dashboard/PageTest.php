<?php

use App\Jobs\TranslateComponent;
use App\Livewire\Dashboard\Page;
use App\Models\Vendor;
use App\Services\ContentLoader;
use App\Services\ContentWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('vendors');
    Queue::fake();
});

it('lists active components from the home page', function () {
    $vendor = Vendor::factory()->live()->create(['slug' => 'demo-bistro']);
    $writer = app(ContentWriter::class);
    $writer->initializeVendor($vendor);
    $writer->fillComponent($vendor, 'home', 'hero', ['image' => 'x', 'title' => 'Demo']);
    $writer->fillComponent($vendor, 'home', 'about', ['section_label' => 'Test'], 'body text');

    Livewire::actingAs($vendor->user)
        ->test(Page::class)
        ->assertSee('hero', escape: false)
        ->assertSee('about', escape: false);
});

it('opens the editor with the current YAML for a component', function () {
    $vendor = Vendor::factory()->live()->create(['slug' => 'demo-bistro']);
    $writer = app(ContentWriter::class);
    $writer->initializeVendor($vendor);
    $writer->fillComponent($vendor, 'home', 'hero', [
        'image' => 'https://x.test/hero.jpg',
        'title' => 'Demo',
        'badge_label' => 'Test',
    ]);

    Livewire::actingAs($vendor->user)
        ->test(Page::class)
        ->call('openEditor', 'hero')
        ->assertSet('editingType', 'hero')
        ->assertSee('title: Demo', escape: false)
        ->assertSee('badge_label: Test', escape: false);
});

it('saves edits via ContentWriter and dispatches translation + feed-updated', function () {
    $vendor = Vendor::factory()->live()->create(['slug' => 'demo-bistro']);
    $writer = app(ContentWriter::class);
    $writer->initializeVendor($vendor);
    $writer->fillComponent($vendor, 'home', 'hero', ['image' => 'a', 'title' => 'Old']);

    Queue::fake();

    $newYaml = "image: 'a'\ntitle: New Title";

    Livewire::actingAs($vendor->user)
        ->test(Page::class)
        ->call('openEditor', 'hero')
        ->set('editingYaml', $newYaml)
        ->call('saveEditor')
        ->assertDispatched('feed-updated')
        ->assertSet('editingType', null);

    $hero = app(ContentLoader::class)->loadComponent('demo-bistro', 'home', 'hero', '01-hero.md');
    expect($hero['fields']['title'])->toBe('New Title');

    Queue::assertPushed(TranslateComponent::class);
});

it('surfaces an error when YAML is invalid', function () {
    $vendor = Vendor::factory()->live()->create(['slug' => 'demo-bistro']);
    $writer = app(ContentWriter::class);
    $writer->initializeVendor($vendor);
    $writer->fillComponent($vendor, 'home', 'hero', ['image' => 'a', 'title' => 'Old']);

    Livewire::actingAs($vendor->user)
        ->test(Page::class)
        ->call('openEditor', 'hero')
        ->set('editingYaml', 'title: { broken')
        ->call('saveEditor')
        ->assertSet('editingType', 'hero')
        ->assertSeeText('YAML could not be parsed');
});
