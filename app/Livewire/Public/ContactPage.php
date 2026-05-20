<?php

namespace App\Livewire\Public;

use App\Models\Conversation;
use App\Models\Vendor;
use App\Services\ContentLoader;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cookie;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use RuntimeException;

/**
 * Public Tourist-Contact-Page (`/{slug}/contact`).
 *
 * Erste Visit: Contact-Form wird gerendert. POST landet bei
 * ContactSubmitController, der eine Conversation anlegt + Cookie setzt
 * und auf /conversations/{token} weiterleitet.
 *
 * Bei Wiederkehr: Cookie `feedai_conversation_{slug}` enthält den Token —
 * wir leiten direkt in die laufende Conversation zurück, damit der Tourist
 * Vendor-Antworten sieht ohne nochmal zu suchen.
 */
#[Title('Contact')]
class ContactPage extends Component
{
    #[Locked]
    public string $slug;

    public function mount(ContentLoader $loader, string $vendor): mixed
    {
        try {
            $loader->loadVendor($vendor);
        } catch (RuntimeException) {
            abort(404);
        }

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

            // Stale cookie (Conversation gelöscht oder Vendor entfernt) — räumen wir auf.
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
