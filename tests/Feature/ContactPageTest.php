<?php

use App\Livewire\Public\ContactPage;
use App\Models\Conversation;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Storage `vendors/demo/vendor.yaml` is published by the project (ContentLoader
 * reads from disk). Using the demo slug means we don't have to seed YAML files
 * inside each test.
 */
beforeEach(function () {
    expect(is_file(storage_path('app/vendors/demo/vendor.yaml')))->toBeTrue(
        'Demo vendor content must be on disk for ContactPage tests'
    );
});

it('renders the contact form for a first-time visitor', function () {
    Vendor::factory()->live()->create(['slug' => 'demo', 'name' => 'Demo Vendor']);

    $response = $this->get('/demo/contact');

    $response->assertOk()
        ->assertSee('Start conversation', escape: false)
        ->assertSee('Message', escape: false);
});

it('redirects returning visitor with a valid cookie to their conversation', function () {
    $vendor = Vendor::factory()->live()->create(['slug' => 'demo']);
    $conversation = Conversation::factory()->create(['vendor_id' => $vendor->id]);

    $this->withCookie(ContactPage::cookieName('demo'), $conversation->token)
        ->get('/demo/contact')
        ->assertRedirect(route('conversations.show', ['conversation' => $conversation->token]));
});

it('ignores cookies that point at a different vendor', function () {
    $vendorA = Vendor::factory()->live()->create(['slug' => 'demo']);
    $vendorB = Vendor::factory()->live()->create(['slug' => 'someone-else']);
    $foreign = Conversation::factory()->create(['vendor_id' => $vendorB->id]);

    $this->withCookie(ContactPage::cookieName('demo'), $foreign->token)
        ->get('/demo/contact')
        ->assertOk()
        ->assertSee('Start conversation', escape: false);
});

it('renders 404 for unknown vendor slug', function () {
    $this->get('/no-such-vendor-here/contact')->assertNotFound();
});

it('contact submit queues a cookie for the vendor slug', function () {
    Vendor::factory()->live()->create(['slug' => 'demo', 'locale' => 'th']);

    $response = $this->post(route('vendor.contact', ['vendor' => 'demo']), [
        'message' => 'Hi there',
        'locale' => 'en',
    ]);

    $conversation = Conversation::first();
    $response->assertRedirect(route('conversations.show', ['conversation' => $conversation->token]));
    $response->assertCookie(ContactPage::cookieName('demo'), $conversation->token);
});
