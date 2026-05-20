<?php

namespace App\Livewire\Dashboard;

use App\Models\Conversation;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Vendor inbox. Lists all conversations, newest first.
 * Shows preview in vendor language (translated_text for tourist messages).
 */
#[Layout('layouts.app')]
#[Title('Inbox')]
class Inbox extends Component
{
    #[Locked]
    public int $vendorId;

    public function mount(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $vendor = $user->vendor;
        abort_unless($vendor instanceof Vendor, 404);

        $this->vendorId = $vendor->id;
    }

    public function render(): View
    {
        $conversations = Conversation::with(['messages' => fn ($q) => $q->latest()->limit(1)])
            ->withCount(['messages as unread_count' => fn ($q) => $q->where('sender', 'tourist')->whereNull('read_at')])
            ->where('vendor_id', $this->vendorId)
            ->orderByDesc('last_message_at')
            ->get();

        return view('livewire.dashboard.inbox', [
            'conversations' => $conversations,
        ]);
    }
}
