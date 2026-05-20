<?php

namespace App\Livewire\Dashboard;

use App\Models\User;
use App\Models\Vendor;
use App\Services\ContentWriter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * Vendor picks an optional accent color for their feed.
 *
 * Null/empty = monochrome (default ink). Writes both to the DB row
 * and the vendor.yaml in storage, so the public feed layout can set
 * the CSS variable `--accent` from the vendor data.
 */
#[Layout('layouts.app')]
#[Title('Accent')]
class AccentSettings extends Component
{
    #[Locked]
    public int $vendorId;

    #[Validate('nullable|regex:/^#[0-9A-Fa-f]{6}$/')]
    public ?string $accentColor = null;

    /**
     * @var array<int, array{value:string, label:string}>
     */
    public array $presets = [
        ['value' => '', 'label' => 'Monochrome (default)'],
        ['value' => '#0F5C5C', 'label' => 'Petrol'],
        ['value' => '#B5483A', 'label' => 'Terracotta'],
        ['value' => '#4C3A8C', 'label' => 'Deep Purple'],
        ['value' => '#2D5A3D', 'label' => 'Forest'],
    ];

    public ?string $savedAt = null;

    public function mount(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $vendor = $user->vendor;
        abort_unless($vendor instanceof Vendor, 404);

        $this->vendorId = $vendor->id;
        $this->accentColor = $vendor->accent_color;
    }

    public function save(): void
    {
        $this->validate();

        $vendor = Vendor::findOrFail($this->vendorId);
        $vendor->forceFill(['accent_color' => $this->accentColor ?: null])->save();

        app(ContentWriter::class)->initializeVendor($vendor->fresh());

        $this->savedAt = now()->format('H:i:s');
        $this->dispatch('feed-updated');
    }

    public function selectPreset(string $value): void
    {
        $this->accentColor = $value === '' ? null : $value;
        $this->save();
    }

    public function render(): View
    {
        return view('livewire.dashboard.accent-settings');
    }
}
