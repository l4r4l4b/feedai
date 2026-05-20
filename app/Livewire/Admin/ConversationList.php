<?php

namespace App\Livewire\Admin;

use App\Models\Conversation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Conversations')]
class ConversationList extends Component
{
    use WithPagination;

    #[Computed]
    public function conversations(): LengthAwarePaginator
    {
        return Conversation::query()
            ->with('vendor:id,name,slug')
            ->withCount('messages')
            ->latest('last_message_at')
            ->paginate(20);
    }

    public function render(): View
    {
        return view('livewire.admin.conversation-list');
    }
}
