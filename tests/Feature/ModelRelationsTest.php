<?php

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vendor;

it('creates a vendor with a user via factory', function () {
    $vendor = Vendor::factory()->create();

    expect($vendor->user)->toBeInstanceOf(User::class)
        ->and($vendor->user->role)->toBe('vendor')
        ->and($vendor->status)->toBe('draft')
        ->and($vendor->locale)->toBe('th');
});

it('user has one vendor relationship', function () {
    $user = User::factory()->vendor()->create();
    $vendor = Vendor::factory()->for($user)->create();

    expect($user->fresh()->vendor->id)->toBe($vendor->id);
});

it('isVendor and isAdmin helpers reflect the role', function () {
    expect(User::factory()->vendor()->create()->isVendor())->toBeTrue()
        ->and(User::factory()->admin()->create()->isAdmin())->toBeTrue()
        ->and(User::factory()->admin()->create()->isVendor())->toBeFalse();
});

it('vendor has many conversations and payments', function () {
    $vendor = Vendor::factory()->create();
    Conversation::factory()->for($vendor)->count(3)->create();
    Payment::factory()->for($vendor)->count(2)->create();

    expect($vendor->conversations)->toHaveCount(3)
        ->and($vendor->payments)->toHaveCount(2);
});

it('conversation auto-generates a unique token', function () {
    $a = Conversation::factory()->create();
    $b = Conversation::factory()->create();

    expect($a->token)->toBeString()->toHaveLength(48)
        ->and($b->token)->not->toBe($a->token);
});

it('conversation has many messages', function () {
    $conversation = Conversation::factory()->create();
    Message::factory()->for($conversation)->fromTourist()->create();
    Message::factory()->for($conversation)->fromVendor()->create();

    expect($conversation->messages)->toHaveCount(2)
        ->and($conversation->messages->pluck('sender')->all())->toContain('tourist', 'vendor');
});

it('message belongs to a conversation', function () {
    $message = Message::factory()->translated()->create();

    expect($message->conversation)->toBeInstanceOf(Conversation::class)
        ->and($message->translated_text)->not->toBeNull()
        ->and($message->translated_locale)->not->toBeNull();
});

it('payment factory states produce the right status', function () {
    expect(Payment::factory()->paid()->create())
        ->status->toBe('paid')
        ->paid_at->not->toBeNull()
        ->provider_reference->toStartWith('cs_test_');

    expect(Payment::factory()->failed()->create()->status)->toBe('failed');
    expect(Payment::factory()->promptpay()->create()->method)->toBe('promptpay');
    expect(Payment::factory()->stablecoin()->create())
        ->method->toBe('stablecoin')
        ->currency->toBe('USDC');
});

it('cascades deletion from user down through vendor to conversations, messages and payments', function () {
    $user = User::factory()->vendor()->create();
    $vendor = Vendor::factory()->for($user)->create();
    $conversation = Conversation::factory()->for($vendor)->create();
    Message::factory()->for($conversation)->count(3)->create();
    Payment::factory()->for($vendor)->count(2)->create();

    expect(Vendor::count())->toBe(1)
        ->and(Conversation::count())->toBe(1)
        ->and(Message::count())->toBe(3)
        ->and(Payment::count())->toBe(2);

    $user->delete();

    expect(Vendor::count())->toBe(0)
        ->and(Conversation::count())->toBe(0)
        ->and(Message::count())->toBe(0)
        ->and(Payment::count())->toBe(0);
});
