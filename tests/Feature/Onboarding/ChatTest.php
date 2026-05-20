<?php

use App\Ai\Agents\ImageAnalyzer;
use App\Ai\Agents\OnboardingAgent;
use App\Livewire\Onboarding\Chat;
use App\Models\OnboardingSession;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Prompts\AgentPrompt;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('vendors');
    Storage::fake('public');
});

it('greets the vendor on mount and dispatches feed-updated', function () {
    $vendor = Vendor::factory()->create(['name' => 'Mae Som']);
    OnboardingSession::factory()->create(['vendor_id' => $vendor->id]);

    Livewire::actingAs($vendor->user)
        ->test(Chat::class)
        ->assertDispatched('feed-updated')
        ->assertSee('Hi Mae Som', escape: false);
});

it('sends a message to the agent and renders the response', function () {
    OnboardingAgent::fake(['Vielen Dank — was machst Du genau?']);

    $vendor = Vendor::factory()->create();
    OnboardingSession::factory()->create(['vendor_id' => $vendor->id]);

    Livewire::actingAs($vendor->user)
        ->test(Chat::class)
        ->set('draft', 'Ich bin Mae Som und verkaufe Pad Thai am Khao San Road.')
        ->call('sendMessage')
        ->assertSee('Pad Thai am Khao San Road', escape: false)
        ->assertSee('Vielen Dank', escape: false)
        ->assertDispatched('feed-updated');

    OnboardingAgent::assertPrompted(
        fn (AgentPrompt $prompt): bool => str_contains($prompt->prompt, 'Ich bin Mae Som')
    );
});

it('ignores empty messages', function () {
    $vendor = Vendor::factory()->create();
    OnboardingSession::factory()->create(['vendor_id' => $vendor->id]);

    Livewire::actingAs($vendor->user)
        ->test(Chat::class)
        ->set('draft', '   ')
        ->call('sendMessage')
        ->assertCount('messages', 1);
});

it('persists an uploaded photo via the ingestor and embeds the vision description in the prompt', function () {
    OnboardingAgent::fake(['Schönes Bild! Soll ich es als Hero verwenden?']);
    ImageAnalyzer::fake([
        [
            'description' => 'Streetfood-Stand mit Wok bei Nacht, Khao San Road',
            'alt_text' => 'Streetfood-Stand bei Nacht',
            'suggested_intent' => 'hero',
            'tags' => ['streetfood', 'wok', 'night'],
            'detected_text' => '',
            'locale_hint' => '',
        ],
    ]);

    $vendor = Vendor::factory()->create();
    OnboardingSession::factory()->create(['vendor_id' => $vendor->id]);

    $component = Livewire::actingAs($vendor->user)
        ->test(Chat::class)
        ->set('photo', UploadedFile::fake()->image('hero.png', 100, 100))
        ->set('draft', 'Hier ist mein Stand')
        ->call('sendMessage');

    $component
        ->assertSee('Streetfood-Stand mit Wok bei Nacht', escape: false)
        ->assertSee('Schönes Bild', escape: false);

    expect($vendor->fresh()->getMedia('images'))->toHaveCount(1);

    // Agent bekommt das strukturierte Image-Block als Teil des Prompts
    OnboardingAgent::assertPrompted(
        fn (AgentPrompt $prompt): bool => str_contains($prompt->prompt, '[IMAGE_UPLOADED')
            && str_contains($prompt->prompt, 'suggested_intent=hero')
            && str_contains($prompt->prompt, 'Streetfood-Stand mit Wok')
    );

    // Custom-Properties sind auf der Media-Row gelandet
    $media = $vendor->fresh()->getMedia('images')->first();
    expect($media->getCustomProperty('description'))->toContain('Streetfood-Stand')
        ->and($media->getCustomProperty('suggested_intent'))->toBe('hero')
        ->and($media->getCustomProperty('tags'))->toContain('streetfood');
});
