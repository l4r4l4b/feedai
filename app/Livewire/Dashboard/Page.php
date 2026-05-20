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
 * Vendor dashboard. Live preview on the left, component list on the right with edit drawer.
 *
 * The drawer is a simple JSON editor — it exposes the raw frontmatter fields
 * as YAML for editing. This lets the vendor edit exactly the same values
 * the AI agent sets via fillComponent.
 */
#[Layout('layouts.app')]
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

        if ($user->isAdmin()) {
            return $this->redirectRoute('admin.dashboard');
        }

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

        // Yaml::dump shows the fields the way they also appear in the MD file
        // (snake_case keys). Bypassing the camelCase transformation in the
        // loader by re-parsing the raw file would be overkill — instead, we
        // convert camel back to snake for display.
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
            $this->editError = 'YAML could not be parsed: '.$e->getMessage();

            return;
        }

        if (! is_array($fields)) {
            $this->editError = 'YAML must be an object, not a scalar.';

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
