<?php

use App\Http\Controllers\Dashboard\StripeReturnController;
use App\Http\Controllers\Public\ContactSubmitController;
use App\Http\Controllers\StripeWebhookController;
use App\Livewire\Dashboard\Inbox;
use App\Livewire\Dashboard\InboxConversation;
use App\Livewire\Dashboard\Page as DashboardPage;
use App\Livewire\Dashboard\Payments\Settings as PaymentSettings;
use App\Livewire\Onboarding\Page as OnboardingPage;
use App\Livewire\Public\ConversationView;
use App\Livewire\PublicPay;
use App\Services\ContentLoader;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/onboarding', OnboardingPage::class)->name('onboarding');
    Route::get('/dashboard', DashboardPage::class)->name('dashboard');
    Route::get('/dashboard/inbox', Inbox::class)->name('inbox');
    Route::get('/dashboard/inbox/{conversation}', InboxConversation::class)
        ->where('conversation', '[A-Za-z0-9]{48}')
        ->name('inbox.show');

    Route::get('/dashboard/payments', PaymentSettings::class)->name('dashboard.payments.settings');
    Route::get('/dashboard/payments/stripe/return', [StripeReturnController::class, 'return'])
        ->name('dashboard.payments.stripe.return');
    Route::get('/dashboard/payments/stripe/refresh', [StripeReturnController::class, 'refresh'])
        ->name('dashboard.payments.stripe.refresh');
});

Route::post('/stripe/webhook', StripeWebhookController::class)->name('stripe.webhook');

Route::get('/conversations/{conversation}', ConversationView::class)
    ->where('conversation', '[A-Za-z0-9]{48}')
    ->name('conversations.show');

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';

/*
|--------------------------------------------------------------------------
| Public Vendor Feed
|--------------------------------------------------------------------------
|
| Catch-all für öffentliche Vendor-Slugs. MUSS am Ende stehen, damit alle
| benannten Routen (auth, dashboard, settings, conversations) zuerst greifen.
| Slug-Pattern schließt zusätzlich reserved prefixes auf Router-Ebene aus.
|
*/

Route::post('/{vendor}/contact', ContactSubmitController::class)
    ->where('vendor', '[a-z0-9][a-z0-9-]*')
    ->name('vendor.contact');

// MUST be registered BEFORE the {vendor}/{page?} catch-all so it wins routing.
Route::get('/{vendor}/pay', PublicPay::class)
    ->where('vendor', '[a-z0-9][a-z0-9-]*')
    ->name('public.pay');

Route::get('/{vendor}/{page?}', function (ContentLoader $loader, string $vendor, string $page = 'home') {
    try {
        $vendorData = $loader->loadVendor($vendor);
        $components = $loader->loadPageComponents($vendor, $page);
    } catch (RuntimeException) {
        abort(404);
    }

    return view('feed.show', [
        'vendor' => $vendorData,
        'page' => $page,
        'components' => $components,
    ]);
})
    ->where('vendor', '[a-z0-9][a-z0-9-]*')
    ->where('page', '[a-z0-9][a-z0-9-]*')
    ->name('feed.show');
