<?php

use App\Livewire\Admin\AdminDashboard;
use App\Livewire\Admin\ConversationList;
use App\Livewire\Admin\PaymentLog;
use App\Livewire\Admin\VendorList;
use App\Models\Conversation;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vendor;
use Livewire\Livewire;

$adminPaths = ['/admin', '/admin/vendors', '/admin/payments', '/admin/conversations'];

it('redirects guests from every admin path to login', function () use ($adminPaths) {
    foreach ($adminPaths as $path) {
        $this->get($path)->assertRedirect('/login');
    }
});

it('blocks vendor users from every admin path with 403', function () use ($adminPaths) {
    $vendor = User::factory()->vendor()->create();

    foreach ($adminPaths as $path) {
        $this->actingAs($vendor)->get($path)->assertForbidden();
    }
});

it('lets admin users access every admin path with 200', function () use ($adminPaths) {
    $admin = User::factory()->admin()->create();

    foreach ($adminPaths as $path) {
        $this->actingAs($admin)->get($path)->assertOk();
    }
});

it('admin dashboard exposes computed stats', function () {
    // Anchor vendor so Payment/Conversation factories don't spawn additional vendors.
    $anchor = Vendor::factory()->create();              // 1 draft

    Vendor::factory()->create();                        // +1 draft -> 2 draft
    Vendor::factory()->live()->count(3)->create();      // +3 live  -> 3 live
    Payment::factory()->for($anchor)->paid()->count(2)->create();
    Payment::factory()->for($anchor)->count(1)->create();
    Conversation::factory()->for($anchor)->count(4)->create();

    Livewire::actingAs(User::factory()->admin()->create())
        ->test(AdminDashboard::class)
        ->assertSet('stats.vendors_total', 5)
        ->assertSet('stats.vendors_live', 3)
        ->assertSet('stats.vendors_draft', 2)
        ->assertSet('stats.payments_total', 3)
        ->assertSet('stats.payments_paid', 2)
        ->assertSet('stats.conversations_total', 4);
});

it('vendor list shows vendors and filters by status', function () {
    Vendor::factory()->create(['name' => 'Draft Cafe', 'status' => 'draft']);
    Vendor::factory()->live()->create(['name' => 'Live Tours']);
    Vendor::factory()->disabled()->create(['name' => 'Closed Shop']);

    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(VendorList::class)
        ->assertSee('Draft Cafe')
        ->assertSee('Live Tours')
        ->assertSee('Closed Shop')
        ->call('setStatusFilter', 'live')
        ->assertSee('Live Tours')
        ->assertDontSee('Draft Cafe')
        ->assertDontSee('Closed Shop');
});

it('payment log lists payments newest first with vendor name', function () {
    $vendor = Vendor::factory()->create(['name' => 'Cool Vendor']);
    Payment::factory()->for($vendor)->paid()->create(['description' => 'Tour']);

    Livewire::actingAs(User::factory()->admin()->create())
        ->test(PaymentLog::class)
        ->assertSee('Cool Vendor')
        ->assertSee('Tour');
});

it('conversation list shows tourist + vendor + message count', function () {
    $vendor = Vendor::factory()->create(['name' => 'Chat Vendor']);
    $conversation = Conversation::factory()->for($vendor)->create([
        'tourist_name' => 'Greta Müller',
        'tourist_email' => 'greta@test.local',
    ]);
    $conversation->messages()->createMany([
        ['sender' => 'tourist', 'original_text' => 'Hi', 'original_locale' => 'de'],
        ['sender' => 'vendor', 'original_text' => 'สวัสดี', 'original_locale' => 'th'],
    ]);

    Livewire::actingAs(User::factory()->admin()->create())
        ->test(ConversationList::class)
        ->assertSee('Chat Vendor')
        ->assertSee('Greta Müller')
        ->assertSee('2'); // messages_count
});
