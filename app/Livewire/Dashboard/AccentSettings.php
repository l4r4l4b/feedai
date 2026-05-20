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
 * Vendor wählt eine optionale Akzent-Farbe für seinen Feed.
 *
 * Null/leer = monochrom (Default ink). Schreibt sowohl in die DB-Row
 * als auch in die vendor.yaml im Storage, damit der Public-Feed-Layout
 * die CSS-Variable `--accent` aus den Vendor-Daten setzen kann.
 */
#[Layout('layouts.app')]
#[Title('Akzent')]
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
        ['value' => '', 'label' => 'Monochrom (Default)'],
        ['value' => '#0F5C5C', 'label' => 'Petrol'],
        ['value' => '#B5483A', 'label' => 'Terrakotta'],
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
