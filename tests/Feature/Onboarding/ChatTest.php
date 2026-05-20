<?php

use App\Ai\Agents\ImageAnalyzer;
use App\Ai\Agents\OnboardingAgent;
use App\Livewire\Onboarding\Chat;
use App\Models\OnboardingSession;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Models\ConversationMessage;
use Laravel\Ai\Prompts\AgentPrompt;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('vendors');
    Storage::fake('public');
});

it('renders the greeting card with the vendor name on mount', function () {
    $vendor = Vendor::factory()->create(['name' => 'Mae Som']);
    OnboardingSession::factory()->create(['vendor_id' => $vendor->id]);

    Livewire::actingAs($vendor->user)
        ->test(Chat::class)
        ->assertSet('vendorName', 'Mae Som')
        ->assertSee('Hi Mae Som', escape: false);
});

it('submitPrompt clears the input and sets pending state', function () {
    $vendor = Vendor::factory()->create();
    OnboardingSession::factory()->create(['vendor_id' => $vendor->id]);

    Livewire::actingAs($vendor->user)
        ->test(Chat::class)
        ->set('draft', 'Hallo aus Bangkok')
        ->call('submitPrompt')
        ->assertSet('draft', '')
        ->assertSet('pendingText', 'Hallo aus Bangkok')
        ->assertSet('queuedPrompt', 'Hallo aus Bangkok');
});

it('runAgent calls the AI and clears pending state', function () {
    OnboardingAgent::fake(['Vielen Dank — was machst Du genau?']);

    $vendor = Vendor::factory()->create();
    OnboardingSession::factory()->create(['vendor_id' => $vendor->id]);

    Livewire::actingAs($vendor->user)
        ->test(Chat::class)
        ->set('draft', 'Ich bin Mae Som und verkaufe Pad Thai am Khao San Road.')
        ->call('submitPrompt')
        ->call('runAgent')
        ->assertSet('pendingText', '')
        ->assertSet('queuedPrompt', '')
        ->assertDispatched('feed-updated')
        ->assertDispatched('chat-scroll');

    expect(ConversationMessage::where('content', 'LIKE', '%Ich bin Mae Som%')->exists())->toBeTrue();
    expect(ConversationMessage::where('role', 'assistant')->where('content', 'Vielen Dank — was machst Du genau?')->exists())->toBeTrue();

    OnboardingAgent::assertPrompted(
        fn (AgentPrompt $prompt): bool => str_contains($prompt->prompt, 'Ich bin Mae Som')
    );
});

it('ignores empty submissions', function () {
    OnboardingAgent::fake([]);

    $vendor = Vendor::factory()->create();
    OnboardingSession::factory()->create(['vendor_id' => $vendor->id]);

    Livewire::actingAs($vendor->user)
        ->test(Chat::class)
        ->set('draft', '   ')
        ->call('submitPrompt')
        ->assertSet('queuedPrompt', '');

    OnboardingAgent::assertNeverPrompted();
});

it('handles multiple photos — each analyzed, each embedded into the prompt', function () {
    OnboardingAgent::fake(['Drei schöne Bilder.']);
    ImageAnalyzer::fake([
        [
            'description' => 'Wok bei Nacht',
            'alt_text' => 'Wok',
            'suggested_intent' => 'hero',
            'tags' => ['streetfood'],
            'detected_text' => '',
            'locale_hint' => '',
        ],
        [
            'description' => 'Pad Thai Goong Teller',
            'alt_text' => 'Pad Thai',
            'suggested_intent' => 'menu_item',
            'tags' => ['food'],
            'detected_text' => '',
            'locale_hint' => '',
        ],
        [
            'description' => 'Markt am Morgen',
            'alt_text' => 'Markt',
            'suggested_intent' => 'gallery',
            'tags' => ['market'],
            'detected_text' => '',
            'locale_hint' => '',
        ],
    ]);

    $vendor = Vendor::factory()->create();
    OnboardingSession::factory()->create(['vendor_id' => $vendor->id]);

    Livewire::actingAs($vendor->user)
        ->test(Chat::class)
        ->set('photos', [
            UploadedFile::fake()->image('one.png', 100, 100),
            UploadedFile::fake()->image('two.png', 100, 100),
            UploadedFile::fake()->image('three.png', 100, 100),
        ])
        ->set('draft', 'Hier ein paar Bilder')
        ->call('submitPrompt')
        ->call('runAgent')
        ->assertSet('photos', []);

    expect($vendor->fresh()->getMedia('images'))->toHaveCount(3);

    OnboardingAgent::assertPrompted(function (AgentPrompt $prompt): bool {
        return substr_count($prompt->prompt, '[IMAGE_UPLOADED') === 3
            && str_contains($prompt->prompt, 'Wok bei Nacht')
            && str_contains($prompt->prompt, 'Pad Thai Goong')
            && str_contains($prompt->prompt, 'Markt am Morgen');
    });
});

it('removePhoto drops one of the queued uploads before send', function () {
    $vendor = Vendor::factory()->create();
    OnboardingSession::factory()->create(['vendor_id' => $vendor->id]);

    $component = Livewire::actingAs($vendor->user)
        ->test(Chat::class)
        ->set('photos', [
            UploadedFile::fake()->image('a.png', 50, 50),
            UploadedFile::fake()->image('b.png', 50, 50),
        ]);

    expect($component->get('photos'))->toHaveCount(2);

    $component->call('removePhoto', 0);

    expect($component->get('photos'))->toHaveCount(1);
});

it('reloads the chat history from the DB after a fresh mount', function () {
    OnboardingAgent::fake(['Hi! Erzähl mir mehr.']);

    $vendor = Vendor::factory()->create();
    OnboardingSession::factory()->create(['vendor_id' => $vendor->id]);

    Livewire::actingAs($vendor->user)
        ->test(Chat::class)
        ->set('draft', 'Erstes Hallo')
        ->call('submitPrompt')
        ->call('runAgent');

    $session = OnboardingSession::where('vendor_id', $vendor->id)->first();
    expect($session->conversation_id)->not->toBeNull();

    Livewire::actingAs($vendor->user)
        ->test(Chat::class)
        ->assertSee('Erstes Hallo', escape: false)
        ->assertSee('Hi! Erzähl mir mehr.', escape: false);
});
