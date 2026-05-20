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
 * Page-Level Component für /onboarding. Rendert das Split-View-Layout
 * mit Live-Preview-iframe links/oben und Chat-Subcomponent rechts/unten.
 *
 * Redirected zum Dashboard wenn das Onboarding bereits abgeschlossen ist.
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
