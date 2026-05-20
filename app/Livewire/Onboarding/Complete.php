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
 * Success page right after `finalizeOnboarding`. Shows three clear next steps:
 *
 * 1. Set up payment methods (primary — the only reason FeedAI gets paid).
 * 2. Test the inbox (run through the tourist chat once yourself).
 * 3. Open the public feed (show off, share).
 *
 * Vendor only lands here directly after finalize. Whoever revisits later
 * sees the same overview — no harm, just a repeat of the options.
 *
 * Onboarding status must be `live`, otherwise redirect back to chat.
 */
#[Layout('layouts.app')]
#[Title('Feed is live')]
class Complete extends Component
{
    #[Locked]
    public string $vendorSlug = '';

    #[Locked]
    public string $vendorName = '';

    public function mount(): mixed
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->isAdmin()) {
            return $this->redirectRoute('admin.dashboard');
        }

        $vendor = $user->vendor;
        abort_unless($vendor instanceof Vendor, 404);

        if ($vendor->status !== 'live') {
            return $this->redirectRoute('onboarding');
        }

        $this->vendorSlug = $vendor->slug;
        $this->vendorName = (string) $vendor->name;

        return null;
    }

    public function render(): View
    {
        return view('livewire.onboarding.complete');
    }
}
