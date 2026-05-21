<?php

use App\Http\Controllers\Dashboard\StripeReturnController;
use App\Http\Controllers\Public\ContactSubmitController;
use App\Http\Controllers\Public\SetLocaleController;
use App\Http\Controllers\StripeWebhookController;
use App\Livewire\Dashboard\AccentSettings;
use App\Livewire\Dashboard\Inbox;
use App\Livewire\Dashboard\InboxConversation;
use App\Livewire\Dashboard\Page as DashboardPage;
use App\Livewire\Dashboard\Payments\Settings as PaymentSettings;
use App\Livewire\Onboarding\Complete as OnboardingComplete;
use App\Livewire\Onboarding\Page as OnboardingPage;
use App\Livewire\Public\ContactPage;
use App\Livewire\Public\ConversationView;
use App\Livewire\PublicPay;
use App\Models\Payment;
use App\Models\Vendor;
use App\Services\ContentLoader;
use App\Support\Locale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Stripe\StripeClient;

Route::get('/', function (ContentLoader $loader) {
    // Cards for the "live now" showcase on the marketing page. Curated
    // allowlist so test registrations during a pitch never accidentally
    // leak onto the public marketing surface; vendors not in this list
    // still get their own /{slug} URL, they just don't appear here.
    $featuredSlugs = [
        'demo',
        'khao-san-coffee',
        'niran-tuktuk',
        'pranee-thai-massage',
        'kru-vee-walks',
        'sailom-boats',
    ];

    $liveVendors = Vendor::where('status', 'live')
        ->whereIn('slug', $featuredSlugs)
        ->orderByRaw('FIELD(slug, '.implode(',', array_map(fn ($s) => "'".$s."'", $featuredSlugs)).')')
        ->get()
        ->map(function (Vendor $vendor) use ($loader): ?array {
            try {
                $components = $loader->loadPageComponents($vendor->slug, 'home');
            } catch (Throwable) {
                return null;
            }

            foreach ($components as $component) {
                if (($component['type'] ?? null) === 'hero') {
                    $image = $component['fields']['image'] ?? null;
                    if (! $image) {
                        return null;
                    }

                    return [
                        'slug' => $vendor->slug,
                        'name' => $vendor->name,
                        'locale' => $vendor->locale,
                        'image' => $image,
                        'location' => $component['fields']['location'] ?? null,
                    ];
                }
            }

            return null;
        })
        ->filter()
        ->values();

    return view('welcome', ['liveVendors' => $liveVendors]);
})->name('home');

Route::get('/locale/{locale}', SetLocaleController::class)
    ->where('locale', '[a-z]{2}')
    ->name('locale.set');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/onboarding', OnboardingPage::class)->name('onboarding');
    Route::get('/onboarding/complete', OnboardingComplete::class)->name('onboarding.complete');
    Route::get('/dashboard', DashboardPage::class)->name('dashboard');
    Route::get('/dashboard/inbox', Inbox::class)->name('inbox');
    Route::get('/dashboard/inbox/{conversation}', InboxConversation::class)
        ->where('conversation', '[A-Za-z0-9]{48}')
        ->name('inbox.show');

    Route::get('/dashboard/accent', AccentSettings::class)->name('dashboard.accent');
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
Route::get('/{vendor}/contact', ContactPage::class)
    ->where('vendor', '[a-z0-9][a-z0-9-]*')
    ->name('public.contact');

Route::get('/{vendor}/pay', PublicPay::class)
    ->where('vendor', '[a-z0-9][a-z0-9-]*')
    ->name('public.pay');

Route::get('/{vendor}/{page?}', function (ContentLoader $loader, Request $request, string $vendor, string $page = 'home') {
    try {
        $vendorData = $loader->loadVendor($vendor);
        $viewerLocale = Locale::resolve($request, $vendorData['locale'] ?? null);
        app()->setLocale($viewerLocale);
        $components = $loader->loadPageComponents($vendor, $page, $viewerLocale);
    } catch (RuntimeException) {
        abort(404);
    }

    // Stripe Checkout success return — `session_id=cs_test_…` in the URL.
    // Verify the session via Stripe, mark our Payment row as paid, then
    // redirect to a clean URL with `demo_paid` so the success banner shows.
    if ($request->filled('session_id') && config('services.stripe.secret')) {
        try {
            $stripe = app(StripeClient::class);
            $session = $stripe->checkout->sessions->retrieve($request->string('session_id')->toString());

            if (($session->payment_status ?? null) === 'paid' && ! empty($session->metadata->payment_id)) {
                Payment::whereKey($session->metadata->payment_id)->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'provider_reference' => $session->payment_intent ?? $session->id,
                ]);

                $amount = (int) ($session->amount_total ?? 0);

                return redirect()->route('feed.show', ['vendor' => $vendor, 'page' => $page === 'home' ? null : $page])
                    ->with('flash.demo_paid', $amount)
                    ->setTargetUrl(
                        route('feed.show', ['vendor' => $vendor, 'page' => $page === 'home' ? null : $page]).'?demo_paid='.$amount
                    );
            }
        } catch (Throwable) {
            // fall through — just render the feed without the receipt
        }
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
