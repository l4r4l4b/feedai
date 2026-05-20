<?php

namespace App\Livewire\Public;

use App\Jobs\TranslateMessage;
use App\Models\Conversation;
use App\Models\Message;
use App\Notifications\TouristMessageReceived;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Tourist-Chat-View. Wird per Token aus der Contact-Form-Submit-Route aufgerufen.
 * Kein Login nötig — der Token in der URL ist Auth+Identity.
 *
 * Zeigt alle Messages der Conversation in der Tourist-Sprache (translated_text
 * fallback auf original_text). Tourist kann weitere Nachrichten posten.
 *
 * Eingebettet im Public-Layout damit die Tourist-Bottom-Nav (Feed/Pay/Contact)
 * verfügbar bleibt — der Contact-Tab markiert sich aktiv.
 */
#[Title('Chat')]
class ConversationView extends Component
{
    #[Locked]
    public string $token;

    public string $draft = '';

    public function mount(string $conversation): void
    {
        $this->token = $conversation;
    }

    public function sendMessage(): void
    {
        $text = trim($this->draft);

        if ($text === '') {
            return;
        }

        $conversation = $this->conversation();
        $this->draft = '';

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender' => 'tourist',
            'original_text' => $text,
            'original_locale' => $conversation->tourist_locale,
        ]);

        $conversation->update(['last_message_at' => now()]);

        TranslateMessage::dispatch($message->id);

        if ($conversation->vendor?->user) {
            Notification::send($conversation->vendor->user, new TouristMessageReceived($conversation));
        }
    }

    public function render(): View
    {
        $conversation = $this->conversation();
        $vendor = $conversation->vendor;

        $vendorArray = [
            'slug' => $vendor->slug,
            'name' => $vendor->name,
            'locale' => $vendor->locale,
            'accent_color' => $vendor->accent_color,
        ];

        return view('livewire.public.conversation-view', [
            'conversation' => $conversation,
            'messages' => $conversation->messages()->orderBy('created_at')->get(),
            'vendor' => $vendorArray,
        ])->layout('layouts.public', [
            'vendor' => $vendorArray,
            'page' => 'contact',
        ]);
    }

    private function conversation(): Conversation
    {
        $conversation = Conversation::with('vendor')->where('token', $this->token)->first();

        abort_unless($conversation !== null, 404);

        return $conversation;
    }
}
