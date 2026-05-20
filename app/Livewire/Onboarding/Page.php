<?php

namespace App\Livewire\Onboarding;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Page-level component for /onboarding. Renders the split-view layout
 * with a live-preview iframe on the left/top and the chat subcomponent
 * on the right/bottom.
 *
 * Redirects to the dashboard once onboarding is already complete.
 */
#[Layout('components.layouts.onboarding')]
#[Title('Onboarding')]
class Page extends Component
{
    #[Locked]
    public string $vendorSlug;

    public function mount(): mixed
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->isAdmin()) {
            return $this->redirectRoute('admin.dashboard');
        }

        $vendor = $user->vendor;
        abort_unless($vendor instanceof Vendor, 404);

        if ($vendor->status === 'live') {
            return $this->redirectRoute('dashboard');
        }

        $this->vendorSlug = $vendor->slug;

        return null;
    }

    public function render(): View
    {
        return view('livewire.onboarding.page');
    }
}
