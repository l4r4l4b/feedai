<?php

namespace App\Livewire\Dashboard;

use App\Jobs\TranslateMessage;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\Vendor;
use App\Notifications\VendorReplied;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Conversation-Chat-View für den Vendor im Dashboard-Inbox.
 *
 * Vendor sieht alle Nachrichten in seiner Sprache (Original bei eigenen,
 * translated_text bei Tourist-Messages). Antworten werden in Vendor-Sprache
 * gespeichert und für den Touristen übersetzt.
 */
#[Layout('layouts.app')]
#[Title('Conversation')]
class InboxConversation extends Component
{
    #[Locked]
    public string $token;

    public string $draft = '';

    public function mount(string $conversation): void
    {
        $this->token = $conversation;
        $this->ensureAccess();
    }

    public function sendReply(): void
    {
        $text = trim($this->draft);

        if ($text === '') {
            return;
        }

        $conversation = $this->ensureAccess();
        $this->draft = '';

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender' => 'vendor',
            'original_text' => $text,
            'original_locale' => $conversation->vendor->locale ?? 'th',
        ]);

        $conversation->update(['last_message_at' => now()]);

        TranslateMessage::dispatch($message->id);

        if ($conversation->tourist_email) {
            Notification::route('mail', $conversation->tourist_email)
                ->notify(new VendorReplied($conversation));
        }
    }

    public function render(): View
    {
        $conversation = $this->ensureAccess();

        $messages = $conversation->messages()->orderBy('created_at')->get();

        $conversation->messages()
            ->where('sender', 'tourist')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('livewire.dashboard.inbox-conversation', [
            'conversation' => $conversation,
            'messages' => $messages,
        ]);
    }

    private function ensureAccess(): Conversation
    {
        /** @var User $user */
        $user = Auth::user();
        $vendor = $user->vendor;
        abort_unless($vendor instanceof Vendor, 404);

        $conversation = Conversation::with('vendor')->where('token', $this->token)->first();
        abort_unless($conversation !== null && $conversation->vendor_id === $vendor->id, 404);

        return $conversation;
    }
}
