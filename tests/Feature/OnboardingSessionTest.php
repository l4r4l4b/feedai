<?php

use App\Models\OnboardingSession;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a session with default in_progress status', function () {
    $session = OnboardingSession::factory()->create();

    expect($session->status)->toBe('in_progress')
        ->and($session->isInProgress())->toBeTrue()
        ->and($session->isFinalized())->toBeFalse()
        ->and($session->conversation_id)->not->toBeNull();
});

it('belongs to a vendor', function () {
    $session = OnboardingSession::factory()->create();

    expect($session->vendor)->not->toBeNull()
        ->and($session->vendor->id)->toBe($session->vendor_id);
});

it('transitions to finalized via factory state', function () {
    $session = OnboardingSession::factory()->finalized()->create();

    expect($session->status)->toBe('finalized')
        ->and($session->isFinalized())->toBeTrue()
        ->and($session->finalized_at)->not->toBeNull();
});

it('cascade-deletes when vendor is deleted', function () {
    $session = OnboardingSession::factory()->create();
    $session->vendor->delete();

    expect(OnboardingSession::find($session->id))->toBeNull();
});
