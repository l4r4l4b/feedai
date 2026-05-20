<?php

namespace App\Livewire\Public;

use App\Jobs\TranslateMessage;
use App\Models\Conversation;
use App\Models\Message;
use App\Notifications\TouristMessageReceived;
use App\Support\Locale;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Tourist chat view. Invoked by token from the contact-form submit route.
 * No login required — the token in the URL serves as auth + identity.
 *
 * Shows all messages of the conversation in the tourist's language
 * (translated_text with fallback to original_text). Tourist can post more
 * messages.
 *
 * Embedded in the public layout so the tourist bottom nav (Feed/Pay/Contact)
 * stays available — the contact tab marks itself active.
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

        $vendor = $this->conversation()->vendor;
        app()->setLocale(Locale::resolve(request(), $vendor?->locale));
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

        $messages = $conversation->messages()->orderBy('created_at')->get();

        // Mark vendor replies as read once the tourist actually loads them.
        $conversation->messages()
            ->where('sender', 'vendor')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('livewire.public.conversation-view', [
            'conversation' => $conversation,
            'messages' => $messages,
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
