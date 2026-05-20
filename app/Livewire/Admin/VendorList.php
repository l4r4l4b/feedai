<?php

namespace App\Livewire\Admin;

use App\Models\Vendor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Vendors')]
class VendorList extends Component
{
    use WithPagination;

    #[Url(as: 'status')]
    public string $statusFilter = 'all';

    public function setStatusFilter(string $status): void
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    #[Computed]
    public function vendors(): LengthAwarePaginator
    {
        return Vendor::query()
            ->with('user:id,email')
            ->when(
                $this->statusFilter !== 'all',
                fn ($q) => $q->where('status', $this->statusFilter),
            )
            ->latest('id')
            ->paginate(15);
    }

    public function render(): View
    {
        return view('livewire.admin.vendor-list');
    }
}
