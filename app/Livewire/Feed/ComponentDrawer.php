<?php

namespace App\Livewire\Feed;

use App\Jobs\TranslateComponent;
use App\Models\User;
use App\Models\Vendor;
use App\Services\ContentLoader;
use App\Services\ContentWriter;
use App\Services\VendorImageIngestor;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\Yaml\Yaml;

/**
 * Right-sliding drawer that lets the vendor fill or edit a single component
 * via a schema-driven form. Opened from iframe skeletons (postMessage) or
 * the dashboard components list.
 *
 * Persists via ContentWriter so the existing translation + storage pipeline
 * applies. Dispatches `feed-updated` so the iframe preview refreshes.
 */
class ComponentDrawer extends Component
{
    use WithFileUploads;

    #[Locked]
    public int $vendorId = 0;

    #[Locked]
    public string $vendorSlug = '';

    public string $activeType = '';

    /** @var array<string, mixed> */
    public array $fields = [];

    public string $body = '';

    /** Per-field uploads keyed by field name. */
    public $uploads = [];

    /** Per-item uploads for array fields, keyed by "field.index". */
    public $itemUploads = [];

    public ?string $error = null;

    public function mount(): void
    {
        /** @var User $user */
        $user = Auth::user();
        $vendor = $user->vendor;

        if ($vendor instanceof Vendor) {
            $this->vendorId = $vendor->id;
            $this->vendorSlug = $vendor->slug;
        }
    }

    #[On('open-component-drawer')]
    public function open(string $type): void
    {
        $this->reset(['fields', 'body', 'uploads', 'itemUploads', 'error']);
        $this->activeType = $type;

        $loader = app(ContentLoader::class);

        try {
            $page = $loader->loadPage($this->vendorSlug, 'home');
            foreach ($page['components'] ?? [] as $component) {
                if (($component['type'] ?? null) === $type) {
                    $loaded = $loader->loadComponent(
                        $this->vendorSlug,
                        'home',
                        $type,
                        $component['file'] ?? '',
                    );
                    $this->fields = $this->snakeKeys($loaded['fields'] ?? []);
                    $this->body = (string) ($loaded['body'] ?? '');
                    break;
                }
            }
        } catch (\Throwable) {
            // No content yet — drawer opens empty for a new component.
        }

        $this->seedDefaults($type);
    }

    public function close(): void
    {
        $this->reset(['activeType', 'fields', 'body', 'uploads', 'itemUploads', 'error']);
    }

    public function addItem(string $field): void
    {
        $current = is_array($this->fields[$field] ?? null) ? $this->fields[$field] : [];
        $current[] = $this->blankItem($this->activeType, $field);
        $this->fields[$field] = $current;
    }

    public function removeItem(string $field, int $index): void
    {
        if (! is_array($this->fields[$field] ?? null)) {
            return;
        }

        $items = $this->fields[$field];
        array_splice($items, $index, 1);
        $this->fields[$field] = array_values($items);
    }

    public function save(VendorImageIngestor $ingestor): void
    {
        if ($this->activeType === '' || $this->vendorId === 0) {
            return;
        }

        $vendor = Vendor::find($this->vendorId);
        if ($vendor === null) {
            $this->error = 'Vendor not found.';

            return;
        }

        // Field-level uploads (e.g. hero.image)
        foreach ($this->uploads as $key => $upload) {
            if (! $upload) {
                continue;
            }
            try {
                $media = $ingestor->fromUpload($vendor, $upload, intent: $this->activeType, source: 'component-drawer', analyzeSync: false);
                $this->fields[$key] = $media->getUrl();
            } catch (\Throwable $e) {
                $this->error = 'Image upload failed: '.$e->getMessage();

                return;
            }
        }

        // Per-item uploads (e.g. menu.items.0.image)
        foreach ($this->itemUploads as $itemKey => $upload) {
            if (! $upload) {
                continue;
            }
            [$field, $index] = explode('.', $itemKey, 2);
            $index = (int) $index;

            try {
                $media = $ingestor->fromUpload($vendor, $upload, intent: $this->activeType.'_item', source: 'component-drawer', analyzeSync: false);
                if (isset($this->fields[$field][$index]) && is_array($this->fields[$field][$index])) {
                    $this->fields[$field][$index]['image'] = $media->getUrl();
                }
            } catch (\Throwable $e) {
                $this->error = 'Image upload failed: '.$e->getMessage();

                return;
            }
        }

        try {
            app(ContentWriter::class)->fillComponent(
                $vendor,
                'home',
                $this->activeType,
                $this->fields,
                $this->body !== '' ? $this->body : null,
            );
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();

            return;
        }

        TranslateComponent::dispatch($this->vendorId, 'home', $this->activeType);

        $this->dispatch('feed-updated');
        $this->close();
    }

    public function render(): View
    {
        return view('livewire.feed.component-drawer', [
            'schema' => $this->loadSchema($this->activeType),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function loadSchema(string $type): array
    {
        if ($type === '') {
            return [];
        }

        $path = config_path("feedai/component-schemas/{$type}.yaml");

        if (! is_file($path)) {
            return [];
        }

        try {
            $parsed = Yaml::parseFile($path);
        } catch (\Throwable) {
            return [];
        }

        return is_array($parsed) ? $parsed : [];
    }

    private function seedDefaults(string $type): void
    {
        $defaults = match ($type) {
            'menu' => ['items' => $this->fields['items'] ?? []],
            'opening_hours' => ['hours' => $this->fields['hours'] ?? []],
            'contact_buttons' => ['buttons' => $this->fields['buttons'] ?? []],
            default => [],
        };

        $this->fields = [...$defaults, ...$this->fields];
    }

    /**
     * @return array<string, mixed>
     */
    private function blankItem(string $type, string $field): array
    {
        return match ("{$type}.{$field}") {
            'menu.items' => ['name' => '', 'price' => '', 'description' => '', 'image' => ''],
            'opening_hours.hours' => ['days' => '', 'time' => ''],
            'contact_buttons.buttons' => ['channel' => 'whatsapp', 'value' => '', 'label' => ''],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $fields
     * @return array<string, mixed>
     */
    private function snakeKeys(array $fields): array
    {
        $out = [];
        foreach ($fields as $key => $value) {
            $snake = strtolower((string) preg_replace('/([a-z])([A-Z])/', '$1_$2', (string) $key));
            $out[$snake] = $value;
        }

        return $out;
    }
}
