<?php

use App\Ai\Agents\OnboardingAgent;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Contracts\Tool;

uses(RefreshDatabase::class);

it('exposes all 9 onboarding tools', function () {
    $vendor = Vendor::factory()->create();
    $agent = new OnboardingAgent($vendor);

    $tools = iterator_to_array($agent->tools());

    expect($tools)->toHaveCount(9);

    foreach ($tools as $tool) {
        expect($tool)->toBeInstanceOf(Tool::class);
    }
});

it('includes vendor-specific context in instructions', function () {
    $vendor = Vendor::factory()->create([
        'slug' => 'mae-som',
        'name' => 'Mae Som',
        'locale' => 'th',
        'status' => 'draft',
    ]);

    $instructions = (string) (new OnboardingAgent($vendor))->instructions();

    expect($instructions)
        ->toContain('mae-som')
        ->toContain('Mae Som')
        ->toContain('th')
        ->toContain('draft');
});

it('includes a component reference section derived from schemas', function () {
    $vendor = Vendor::factory()->create();
    $instructions = (string) (new OnboardingAgent($vendor))->instructions();

    // Spot-check a representative subset across all tiers
    expect($instructions)
        ->toContain('hero')
        ->toContain('about')
        ->toContain('menu')
        ->toContain('contact_buttons')
        ->toContain('image_divider')
        ->toContain('highlight_card')
        ->toContain('contact_form')
        ->toContain('pay_now_trigger');
});

it('communicates the one-question-per-turn rule', function () {
    $vendor = Vendor::factory()->create();
    $instructions = (string) (new OnboardingAgent($vendor))->instructions();

    expect($instructions)
        ->toContain('one question')
        ->toContain('initializeVendorFeed')
        ->toContain('finalizeOnboarding')
        ->toContain('Phased Workflow')
        ->toContain('Magazine Editorial Voice');
});

it('responds to prompts when faked', function () {
    OnboardingAgent::fake([
        'Hallo! Wie heißt dein Business und was machst du?',
    ]);

    $vendor = Vendor::factory()->create();
    $response = (new OnboardingAgent($vendor))->prompt('Hi');

    expect((string) $response)->toContain('Wie heißt dein Business');

    OnboardingAgent::assertPrompted('Hi');
});
