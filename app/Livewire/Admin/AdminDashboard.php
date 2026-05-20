<?php

namespace App\Livewire\Admin;

use App\Models\Conversation;
use App\Models\Payment;
use App\Models\Vendor;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Admin')]
class AdminDashboard extends Component
{
    #[Computed]
    public function stats(): array
    {
        return [
            'vendors_total' => Vendor::count(),
            'vendors_live' => Vendor::where('status', 'live')->count(),
            'vendors_draft' => Vendor::where('status', 'draft')->count(),
            'payments_total' => Payment::count(),
            'payments_paid' => Payment::where('status', 'paid')->count(),
            'conversations_total' => Conversation::count(),
        ];
    }

    public function render(): View
    {
        return view('livewire.admin.admin-dashboard');
    }
}
