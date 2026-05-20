<?php

namespace App\Livewire\Dashboard;

use App\Jobs\TranslateComponent;
use App\Models\User;
use App\Models\Vendor;
use App\Services\ContentLoader;
use App\Services\ContentWriter;
use App\Support\Locale;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * Vendor brand settings — accent color + source language. The language is
 * the *write* locale: vendor writes in it, translations to the other two
 * are generated automatically via TranslateComponent. Switching it kicks
 * off a synchronous re-translation of every active component.
 */
#[Layout('layouts.app')]
#[Title('Brand')]
class AccentSettings extends Component
{
    #[Locked]
    public int $vendorId;

    #[Validate('nullable|regex:/^#[0-9A-Fa-f]{6}$/')]
    public ?string $accentColor = null;

    #[Validate(['locale' => 'required|in:en,de,th'])]
    public string $locale = 'en';

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

    /**
     * @var array<int, array{value:string, label:string}>
     */
    public array $localeChoices = [
        ['value' => 'en', 'label' => 'English'],
        ['value' => 'de', 'label' => 'Deutsch'],
        ['value' => 'th', 'label' => 'ไทย (Thai)'],
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
        $this->locale = Locale::isSupported($vendor->locale) ? $vendor->locale : 'en';
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

    /**
     * Persist the new source locale and re-translate every active component
     * so the existing English/German/Thai files stay consistent with the
     * new source language. Re-translation runs through the queue — when
     * a worker is up, the iframe shows the updated content on next reload.
     */
    public function changeLocale(): void
    {
        $this->validate(['locale' => 'required|in:en,de,th']);

        $vendor = Vendor::findOrFail($this->vendorId);
        if ($vendor->locale === $this->locale) {
            return;
        }

        $previousLocale = $vendor->locale;
        $vendor->forceFill(['locale' => $this->locale])->save();
        app(ContentWriter::class)->initializeVendor($vendor->fresh());

        // Stale per-locale translations from the OLD source are dropped so
        // the public renderer falls back to source until the new ones land.
        $disk = Storage::disk('vendors');
        foreach (Locale::SUPPORTED as $target) {
            if ($target === $this->locale) {
                continue;
            }
            $disk->deleteDirectory("{$vendor->slug}/translations/{$target}");
        }

        // Dispatch translation jobs for every active component on every page.
        try {
            $loader = app(ContentLoader::class);
            $page = $loader->loadPage($vendor->slug, 'home');
            foreach ($page['components'] ?? [] as $component) {
                if ($type = $component['type'] ?? null) {
                    TranslateComponent::dispatch($vendor->id, 'home', $type);
                }
            }
        } catch (\Throwable) {
            // No active components yet — nothing to translate.
        }

        $this->dispatch('feed-updated');
        Flux::toast(
            variant: 'success',
            text: __('Source language changed from :from to :to. Translations are being regenerated.', [
                'from' => strtoupper($previousLocale),
                'to' => strtoupper($this->locale),
            ]),
        );
    }

    public function render(): View
    {
        return view('livewire.dashboard.accent-settings');
    }
}
