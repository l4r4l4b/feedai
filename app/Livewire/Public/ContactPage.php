<?php

namespace App\Livewire\Public;

use App\Models\Conversation;
use App\Models\Vendor;
use App\Services\ContentLoader;
use App\Support\Locale;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cookie;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use RuntimeException;

/**
 * Public tourist contact page (`/{slug}/contact`).
 *
 * First visit: the contact form is rendered. POST lands at
 * ContactSubmitController, which creates a conversation + sets a cookie
 * and redirects to /conversations/{token}.
 *
 * On return: cookie `feedai_conversation_{slug}` contains the token —
 * we redirect straight to the running conversation so the tourist sees
 * vendor replies without searching again.
 */
#[Title('Contact')]
class ContactPage extends Component
{
    #[Locked]
    public string $slug;

    public function mount(ContentLoader $loader, string $vendor): mixed
    {
        try {
            $vendorData = $loader->loadVendor($vendor);
        } catch (RuntimeException) {
            abort(404);
        }

        app()->setLocale(Locale::resolve(request(), $vendorData['locale'] ?? null));

        $this->slug = $vendor;

        $cookieName = self::cookieName($vendor);
        $existingToken = request()->cookie($cookieName);

        if (is_string($existingToken) && $existingToken !== '') {
            $vendorModel = Vendor::where('slug', $vendor)->first();

            if ($vendorModel !== null) {
                $conversation = Conversation::where('token', $existingToken)
                    ->where('vendor_id', $vendorModel->id)
                    ->first();

                if ($conversation !== null) {
                    return redirect()->route('conversations.show', ['conversation' => $conversation->token]);
                }
            }

            // Stale cookie (conversation deleted or vendor removed) — clean it up.
            Cookie::queue(Cookie::forget($cookieName));
        }

        return null;
    }

    public function render(ContentLoader $loader): View
    {
        $vendorData = $loader->loadVendor($this->slug);

        return view('livewire.public.contact-page', [
            'vendor' => $vendorData,
        ])->layout('layouts.public', [
            'vendor' => $vendorData,
            'page' => 'contact',
        ]);
    }

    public static function cookieName(string $vendorSlug): string
    {
        return 'feedai_conversation_'.preg_replace('/[^a-z0-9_-]/i', '_', $vendorSlug);
    }
}
