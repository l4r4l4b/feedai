<?php

use App\Ai\Agents\MessageTranslator;
use App\Jobs\TranslateMessage;
use App\Livewire\Dashboard\Inbox;
use App\Livewire\Dashboard\InboxConversation;
use App\Livewire\Public\ConversationView;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Vendor;
use App\Notifications\TouristMessageReceived;
use App\Notifications\VendorReplied;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('public contact form submit creates conversation + message + dispatches translation + notifies vendor', function () {
    Queue::fake();
    Notification::fake();

    $vendor = Vendor::factory()->live()->create(['slug' => 'demo-shop', 'locale' => 'th']);

    $response = $this->post(route('vendor.contact', ['vendor' => 'demo-shop']), [
        'name' => 'Lena',
        'email' => 'lena@example.test',
        'message' => 'Hello, do you have vegan options?',
        'locale' => 'en',
    ]);

    $conversation = Conversation::where('vendor_id', $vendor->id)->first();
    expect($conversation)->not->toBeNull()
        ->and($conversation->tourist_name)->toBe('Lena')
        ->and($conversation->tourist_locale)->toBe('en');

    $message = $conversation->messages()->first();
    expect($message)
        ->not->toBeNull()
        ->and($message->sender)->toBe('tourist')
        ->and($message->original_text)->toBe('Hello, do you have vegan options?');

    $response->assertRedirect(route('conversations.show', ['conversation' => $conversation->token]));

    Queue::assertPushed(TranslateMessage::class, fn (TranslateMessage $job): bool => $job->messageId === $message->id);
    Notification::assertSentTo($vendor->user, TouristMessageReceived::class);
});

it('TranslateMessage stores translated text for tourist message', function () {
    MessageTranslator::fake(['สวัสดี ลูกค้า — ขอบคุณที่ติดต่อ']);

    $vendor = Vendor::factory()->create(['locale' => 'th']);
    $conversation = Conversation::factory()->create([
        'vendor_id' => $vendor->id,
        'tourist_locale' => 'en',
    ]);
    $message = Message::factory()->create([
        'conversation_id' => $conversation->id,
        'sender' => 'tourist',
        'original_text' => 'Hi, what time do you open?',
        'original_locale' => 'en',
        'translated_text' => null,
        'translated_locale' => null,
    ]);

    (new TranslateMessage($message->id))->handle();

    $fresh = $message->fresh();
    expect($fresh->translated_text)->toContain('สวัสดี')
        ->and($fresh->translated_locale)->toBe('th');
});

it('TranslateMessage is a no-op if source == target', function () {
    $vendor = Vendor::factory()->create(['locale' => 'en']);
    $conversation = Conversation::factory()->create([
        'vendor_id' => $vendor->id,
        'tourist_locale' => 'en',
    ]);
    $message = Message::factory()->create([
        'conversation_id' => $conversation->id,
        'sender' => 'tourist',
        'original_text' => 'Same language',
        'original_locale' => 'en',
        'translated_text' => null,
        'translated_locale' => null,
    ]);

    (new TranslateMessage($message->id))->handle();

    $fresh = $message->fresh();
    expect($fresh->translated_text)->toBe('Same language')
        ->and($fresh->translated_locale)->toBe('en');
});

it('public ConversationView renders messages with tourist locale translations', function () {
    $vendor = Vendor::factory()->create(['name' => 'Mae Som', 'locale' => 'th']);
    $conversation = Conversation::factory()->create([
        'vendor_id' => $vendor->id,
        'tourist_locale' => 'en',
    ]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'sender' => 'tourist',
        'original_text' => 'Hi there!',
        'original_locale' => 'en',
    ]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'sender' => 'vendor',
        'original_text' => 'สวัสดี! ขอบคุณ',
        'original_locale' => 'th',
        'translated_text' => 'Hi! Thanks for reaching out.',
        'translated_locale' => 'en',
    ]);

    Livewire::test(ConversationView::class, ['conversation' => $conversation->token])
        ->assertOk()
        ->assertSee('Hi there!', escape: false)
        ->assertSee('Hi! Thanks for reaching out.', escape: false)
        ->assertSee('Mae Som', escape: false);
});

it('public ConversationView posts a message and dispatches translation', function () {
    Queue::fake();

    $vendor = Vendor::factory()->create(['locale' => 'th']);
    $conversation = Conversation::factory()->create([
        'vendor_id' => $vendor->id,
        'tourist_locale' => 'en',
    ]);

    Livewire::test(ConversationView::class, ['conversation' => $conversation->token])
        ->set('draft', 'When are you open today?')
        ->call('sendMessage');

    expect($conversation->messages()->where('original_text', 'When are you open today?')->exists())->toBeTrue();
    Queue::assertPushed(TranslateMessage::class);
});

it('vendor inbox lists their conversations with preview in vendor locale', function () {
    $vendor = Vendor::factory()->live()->create(['locale' => 'th']);
    $conversation = Conversation::factory()->create([
        'vendor_id' => $vendor->id,
        'tourist_locale' => 'en',
        'tourist_name' => 'Lena',
        'last_message_at' => now(),
    ]);
    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'sender' => 'tourist',
        'original_text' => 'Hello!',
        'original_locale' => 'en',
        'translated_text' => 'สวัสดี!',
        'translated_locale' => 'th',
    ]);

    Livewire::actingAs($vendor->user)
        ->test(Inbox::class)
        ->assertOk()
        ->assertSee('Lena', escape: false)
        ->assertSee('สวัสดี!', escape: false);
});

it('vendor InboxConversation lets vendor reply and dispatches translation + email', function () {
    Queue::fake();
    Notification::fake();

    $vendor = Vendor::factory()->live()->create(['locale' => 'th']);
    $conversation = Conversation::factory()->create([
        'vendor_id' => $vendor->id,
        'tourist_locale' => 'en',
        'tourist_email' => 'lena@example.test',
    ]);

    Livewire::actingAs($vendor->user)
        ->test(InboxConversation::class, ['conversation' => $conversation->token])
        ->set('draft', 'สวัสดี — เปิด 17:00 ครับ')
        ->call('sendReply');

    $reply = $conversation->messages()->where('sender', 'vendor')->first();
    expect($reply)->not->toBeNull()
        ->and($reply->original_text)->toBe('สวัสดี — เปิด 17:00 ครับ')
        ->and($reply->original_locale)->toBe('th');

    Queue::assertPushed(TranslateMessage::class);
    Notification::assertSentOnDemand(VendorReplied::class);
});

it('vendor cannot access another vendor conversation', function () {
    $vendorA = Vendor::factory()->live()->create();
    $vendorB = Vendor::factory()->live()->create();
    $conversation = Conversation::factory()->create(['vendor_id' => $vendorB->id]);

    $this->actingAs($vendorA->user)
        ->get(route('inbox.show', ['conversation' => $conversation->token]))
        ->assertNotFound();
});

it('public conversation route 404s for unknown tokens', function () {
    $this->get('/conversations/'.str_repeat('x', 48))->assertNotFound();
});
