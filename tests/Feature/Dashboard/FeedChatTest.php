<?php

use App\Ai\Agents\EditAgent;
use App\Livewire\Dashboard\FeedChat;
use App\Models\User;
use App\Models\Vendor;
use App\Services\ContentWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('vendors');
});

it('renders the intro card for a fresh dashboard chat', function () {
    $vendor = Vendor::factory()->live()->create();
    app(ContentWriter::class)->initializeVendor($vendor);

    Livewire::actingAs($vendor->user)
        ->test(FeedChat::class)
        ->assertOk()
        ->assertSee('What should we change?', escape: false);
});

it('clears input and shows pending bubble on submitPrompt', function () {
    EditAgent::fake(['Done. Anything else?']);

    $vendor = Vendor::factory()->live()->create();
    app(ContentWriter::class)->initializeVendor($vendor);

    Livewire::actingAs($vendor->user)
        ->test(FeedChat::class)
        ->set('draft', 'Change the hero title to Tuk-Tuk with Charlie')
        ->call('submitPrompt')
        ->assertSet('draft', '')
        ->assertSet('pendingText', 'Change the hero title to Tuk-Tuk with Charlie')
        ->assertSet('queuedPrompt', 'Change the hero title to Tuk-Tuk with Charlie');
});

it('persists conversation id on vendor after first runAgent', function () {
    EditAgent::fake(['Done. Anything else?']);

    $vendor = Vendor::factory()->live()->create();
    app(ContentWriter::class)->initializeVendor($vendor);

    expect($vendor->edit_agent_conversation_id)->toBeNull();

    Livewire::actingAs($vendor->user)
        ->test(FeedChat::class)
        ->set('draft', 'Test')
        ->call('submitPrompt')
        ->call('runAgent')
        ->assertDispatched('feed-updated');

    expect($vendor->fresh()->edit_agent_conversation_id)->not->toBeNull();
});

it('ignores empty submissions', function () {
    $vendor = Vendor::factory()->live()->create();
    app(ContentWriter::class)->initializeVendor($vendor);

    Livewire::actingAs($vendor->user)
        ->test(FeedChat::class)
        ->set('draft', '   ')
        ->call('submitPrompt')
        ->assertSet('pendingText', '')
        ->assertSet('queuedPrompt', '');
});

it('aborts 404 for users without a vendor', function () {
    $user = User::factory()->create(['role' => 'vendor']);

    Livewire::actingAs($user)
        ->test(FeedChat::class)
        ->assertStatus(404);
});
