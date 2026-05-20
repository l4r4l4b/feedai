<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Jobs\TranslateMessage;
use App\Livewire\Public\ContactPage;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Vendor;
use App\Notifications\TouristMessageReceived;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * Empfängt das Public-Contact-Form auf der Vendor-Feed-Seite, legt eine
 * neue Conversation an, speichert die erste Tourist-Nachricht und stößt
 * Translation + E-Mail-Benachrichtigung an den Vendor an.
 */
class ContactSubmitController extends Controller
{
    public function __invoke(Request $request, string $vendor): RedirectResponse
    {
        $vendorModel = Vendor::where('slug', $vendor)->firstOrFail();

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:4000'],
            'locale' => ['nullable', 'string', 'in:en,de,th'],
        ]);

        $touristLocale = $validated['locale'] ?? $request->getPreferredLanguage(['en', 'de', 'th']) ?? 'en';

        $conversation = DB::transaction(function () use ($vendorModel, $validated, $touristLocale): Conversation {
            $conversation = Conversation::create([
                'vendor_id' => $vendorModel->id,
                'tourist_locale' => $touristLocale,
                'tourist_name' => $validated['name'] ?? null,
                'tourist_email' => $validated['email'] ?? null,
                'last_message_at' => now(),
            ]);

            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender' => 'tourist',
                'original_text' => $validated['message'],
                'original_locale' => $touristLocale,
            ]);

            TranslateMessage::dispatch($message->id);

            return $conversation;
        });

        if ($vendorModel->user) {
            Notification::send($vendorModel->user, new TouristMessageReceived($conversation));
        }

        // Cookie merkt sich die Conversation pro Vendor-Slug — beim erneuten
        // Aufruf von /{slug}/contact landet der Tourist direkt im laufenden Chat.
        // 90 Tage HttpOnly damit JS nicht hinkommt, encrypted via Laravel default.
        Cookie::queue(
            ContactPage::cookieName($vendor),
            $conversation->token,
            60 * 24 * 90,
        );

        return redirect()->route('conversations.show', ['conversation' => $conversation->token]);
    }
}
