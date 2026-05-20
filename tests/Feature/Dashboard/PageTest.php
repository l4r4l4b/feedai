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

it('embeds the builder-mode iframe so the vendor sees their feed inline', function () {
    $vendor = Vendor::factory()->live()->create(['slug' => 'demo-bistro']);
    $writer = app(ContentWriter::class);
    $writer->initializeVendor($vendor);
    $writer->fillComponent($vendor, 'home', 'hero', ['image' => 'x', 'title' => 'Demo']);

    Livewire::actingAs($vendor->user)
        ->test(Page::class)
        ->assertSee('/demo-bistro?builder=1', escape: false);
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
