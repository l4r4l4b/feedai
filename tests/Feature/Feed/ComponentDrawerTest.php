<?php

use App\Jobs\TranslateComponent;
use App\Livewire\Feed\ComponentDrawer;
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

it('starts with no active component', function () {
    $vendor = Vendor::factory()->live()->create();

    Livewire::actingAs($vendor->user)
        ->test(ComponentDrawer::class)
        ->assertSet('activeType', '');
});

it('opens with empty fields for a new component', function () {
    $vendor = Vendor::factory()->live()->create();
    app(ContentWriter::class)->initializeVendor($vendor);

    Livewire::actingAs($vendor->user)
        ->test(ComponentDrawer::class)
        ->dispatch('open-component-drawer', type: 'hero')
        ->assertSet('activeType', 'hero')
        ->assertSet('fields', []);
});

it('loads existing fields when opening a filled component', function () {
    $vendor = Vendor::factory()->live()->create(['slug' => 'demo-bistro']);
    $writer = app(ContentWriter::class);
    $writer->initializeVendor($vendor);
    $writer->fillComponent($vendor, 'home', 'hero', [
        'image' => 'https://x.test/hero.jpg',
        'title' => 'Mae Som',
        'location' => 'Khao San Road',
    ]);

    Livewire::actingAs($vendor->user)
        ->test(ComponentDrawer::class)
        ->dispatch('open-component-drawer', type: 'hero')
        ->assertSet('activeType', 'hero')
        ->assertSet('fields.title', 'Mae Som')
        ->assertSet('fields.location', 'Khao San Road');
});

it('appends and removes items for array fields', function () {
    $vendor = Vendor::factory()->live()->create();
    app(ContentWriter::class)->initializeVendor($vendor);

    Livewire::actingAs($vendor->user)
        ->test(ComponentDrawer::class)
        ->dispatch('open-component-drawer', type: 'menu')
        ->call('addItem', 'items')
        ->call('addItem', 'items')
        ->assertCount('fields.items', 2)
        ->call('removeItem', 'items', 0)
        ->assertCount('fields.items', 1);
});

it('saves through ContentWriter and dispatches feed-updated', function () {
    $vendor = Vendor::factory()->live()->create(['slug' => 'demo-bistro']);
    app(ContentWriter::class)->initializeVendor($vendor);

    Livewire::actingAs($vendor->user)
        ->test(ComponentDrawer::class)
        ->dispatch('open-component-drawer', type: 'hero')
        ->set('fields.image', 'https://x.test/h.jpg')
        ->set('fields.title', 'New Hero')
        ->call('save')
        ->assertDispatched('feed-updated')
        ->assertSet('activeType', '');

    $hero = app(ContentLoader::class)->loadComponent('demo-bistro', 'home', 'hero', '01-hero.md');
    expect($hero['fields']['title'])->toBe('New Hero');

    Queue::assertPushed(TranslateComponent::class);
});

it('close action resets state', function () {
    $vendor = Vendor::factory()->live()->create();
    app(ContentWriter::class)->initializeVendor($vendor);

    Livewire::actingAs($vendor->user)
        ->test(ComponentDrawer::class)
        ->dispatch('open-component-drawer', type: 'hero')
        ->set('fields.title', 'Hi')
        ->call('close')
        ->assertSet('activeType', '')
        ->assertSet('fields', []);
});
