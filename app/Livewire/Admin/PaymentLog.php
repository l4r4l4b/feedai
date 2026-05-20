<?php

namespace App\Livewire\Admin;

use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Payment Log')]
class PaymentLog extends Component
{
    use WithPagination;

    #[Computed]
    public function payments(): LengthAwarePaginator
    {
        return Payment::query()
            ->with('vendor:id,name,slug')
            ->latest('id')
            ->paginate(20);
    }

    public function render(): View
    {
        return view('livewire.admin.payment-log');
    }
}
