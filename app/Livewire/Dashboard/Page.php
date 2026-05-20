<?php

namespace App\Livewire\Dashboard;

use App\Jobs\TranslateComponent;
use App\Models\User;
use App\Models\Vendor;
use App\Services\ContentLoader;
use App\Services\ContentWriter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Symfony\Component\Yaml\Yaml;

/**
 * Vendor-Dashboard. Live-Preview links, Component-Liste rechts mit Edit-Drawer.
 *
 * Der Drawer ist ein simpler JSON-Editor — er gibt die rohen Frontmatter-Felder
 * als YAML zum Bearbeiten frei. Damit kann der Vendor exakt dieselben Werte
 * editieren, die der AI-Agent über fillComponent setzt.
 */
#[Layout('components.layouts.onboarding')]
#[Title('Dashboard')]
class Page extends Component
{
    #[Locked]
    public int $vendorId;

    #[Locked]
    public string $vendorSlug;

    public ?string $editingType = null;

    public string $editingYaml = '';

    public ?string $editingBody = null;

    public ?string $editError = null;

    public function mount(): mixed
    {
        /** @var User $user */
        $user = Auth::user();

        $vendor = $user->vendor;
        abort_unless($vendor instanceof Vendor, 404);

        if ($vendor->status === 'draft') {
            return $this->redirectRoute('onboarding');
        }

        $this->vendorId = $vendor->id;
        $this->vendorSlug = $vendor->slug;

        return null;
    }

    public function render(): View
    {
        $loader = app(ContentLoader::class);

        $components = [];
        try {
            $page = $loader->loadPage($this->vendorSlug, 'home');
            foreach ($page['components'] ?? [] as $component) {
                $components[] = $component;
            }
        } catch (\Throwable) {
            $components = [];
        }

        return view('livewire.dashboard.page', [
            'components' => $components,
        ]);
    }

    public function openEditor(string $type): void
    {
        $loader = app(ContentLoader::class);
        $this->editError = null;

        $page = $loader->loadPage($this->vendorSlug, 'home');
        $file = null;

        foreach ($page['components'] ?? [] as $component) {
            if (($component['type'] ?? null) === $type) {
                $file = $component['file'];
                break;
            }
        }

        if ($file === null) {
            $this->editError = "Component '{$type}' is not active.";

            return;
        }

        $loaded = $loader->loadComponent($this->vendorSlug, 'home', $type, $file);

        // Yaml::dump zeigt die Felder so wie sie auch in der MD-Datei stehen
        // (snake_case Keys). Die camelCase-Transformation im Loader wird hier
        // umgangen indem wir das raw-File neu parsen wäre overkill — wir
        // konvertieren stattdessen camel zurück nach snake für die Anzeige.
        $rawFields = $this->camelToSnakeKeys($loaded['fields']);

        $this->editingType = $type;
        $this->editingYaml = trim(Yaml::dump($rawFields, 4, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK));
        $this->editingBody = $loaded['body'] !== '' ? $loaded['body'] : null;
    }

    public function closeEditor(): void
    {
        $this->editingType = null;
        $this->editingYaml = '';
        $this->editingBody = null;
        $this->editError = null;
    }

    public function saveEditor(): void
    {
        if ($this->editingType === null) {
            return;
        }

        try {
            /** @var array<string, mixed>|null $fields */
            $fields = Yaml::parse($this->editingYaml) ?? [];
        } catch (\Throwable $e) {
            $this->editError = 'YAML konnte nicht geparst werden: '.$e->getMessage();

            return;
        }

        if (! is_array($fields)) {
            $this->editError = 'YAML muss ein Objekt sein, kein Skalar.';

            return;
        }

        try {
            $vendor = Vendor::findOrFail($this->vendorId);
            app(ContentWriter::class)->fillComponent(
                $vendor,
                'home',
                $this->editingType,
                $fields,
                $this->editingBody,
            );
        } catch (\Throwable $e) {
            $this->editError = $e->getMessage();

            return;
        }

        TranslateComponent::dispatch($this->vendorId, 'home', $this->editingType);

        $this->closeEditor();
        $this->dispatch('feed-updated');
    }

    /**
     * @param  array<string, mixed>  $fields
     * @return array<string, mixed>
     */
    private function camelToSnakeKeys(array $fields): array
    {
        $out = [];

        foreach ($fields as $key => $value) {
            $snake = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $key) ?? $key);
            $out[$snake] = $value;
        }

        return $out;
    }
}
