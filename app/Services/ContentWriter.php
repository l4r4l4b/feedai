<?php

namespace App\Services;

use App\Models\Vendor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;

/**
 * Writes vendor content to storage/app/vendors/{slug}/.
 *
 * Counterpart to ContentLoader. Used by AI tools and the direct-edit
 * drawer. Writes atomically (tmp file + rename) so readers never hit
 * half-written files.
 *
 * Snake-case frontmatter keys are preserved in YAML/MD (convention).
 * The camelCase transformation only happens on read via ContentLoader.
 */
class ContentWriter
{
    public function __construct(private readonly string $disk = 'vendors') {}

    /**
     * Creates the initial storage skeleton for a new vendor.
     *
     * Generates vendor.yaml + an empty pages/home.yaml with template reference.
     * No components active — the AI activates them step by step.
     */
    public function initializeVendor(Vendor $vendor, string $template = 'default'): void
    {
        $slug = $vendor->slug;

        // Always update metadata — AI may change name/locale/accent later.
        $this->writeYaml("{$slug}/vendor.yaml", [
            'slug' => $vendor->slug,
            'name' => $vendor->name,
            'template' => $template,
            'locale' => $vendor->locale,
            'status' => $vendor->status,
            'accent_color' => $vendor->accent_color,
        ]);

        // Only create home page if it does not exist yet — otherwise already
        // activated components would be lost on a re-init.
        $homePath = "{$slug}/pages/home.yaml";
        if (! Storage::disk($this->disk)->exists($homePath)) {
            $this->writeYaml($homePath, [
                'slug' => 'home',
                'components' => [],
            ]);
        }
    }

    /**
     * Activates a component on a page (adds it to the components list).
     *
     * Idempotent — activating twice does not throw. File is created if
     * not yet present (with empty frontmatter).
     */
    public function activateComponent(Vendor $vendor, string $pageSlug, string $type): string
    {
        $page = $this->readPage($vendor->slug, $pageSlug);
        $page['slug'] ??= $pageSlug;
        $page['components'] ??= [];

        foreach ($page['components'] as $existing) {
            if (($existing['type'] ?? null) === $type) {
                return $existing['file'];
            }
        }

        $index = count($page['components']) + 1;
        $file = sprintf('%02d-%s.md', $index, str_replace('_', '-', $type));

        $page['components'][] = [
            'type' => $type,
            'file' => $file,
        ];

        $this->writeYaml("{$vendor->slug}/pages/{$pageSlug}.yaml", $page);

        $contentPath = "{$vendor->slug}/content/{$pageSlug}/{$file}";

        if (! Storage::disk($this->disk)->exists($contentPath)) {
            $this->writeMarkdown($contentPath, [], '');
        }

        return $file;
    }

    /**
     * Deactivates a component — removes it from the list, the content file is preserved.
     */
    public function deactivateComponent(Vendor $vendor, string $pageSlug, string $type): void
    {
        $page = $this->readPage($vendor->slug, $pageSlug);
        $page['components'] = array_values(array_filter(
            $page['components'] ?? [],
            fn (array $component): bool => ($component['type'] ?? null) !== $type,
        ));

        $this->writeYaml("{$vendor->slug}/pages/{$pageSlug}.yaml", $page);
    }

    /**
     * Fills a component with data. Validates against schema.
     *
     * Existing body is preserved when no new one is provided.
     *
     * @param  array<string, mixed>  $fields
     */
    public function fillComponent(
        Vendor $vendor,
        string $pageSlug,
        string $type,
        array $fields,
        ?string $body = null,
    ): string {
        $this->assertSchemaCompliance($type, $fields);

        $file = $this->activateComponent($vendor, $pageSlug, $type);
        $path = "{$vendor->slug}/content/{$pageSlug}/{$file}";

        $existingBody = '';
        if ($body === null && Storage::disk($this->disk)->exists($path)) {
            $existingBody = $this->extractBody(Storage::disk($this->disk)->get($path));
        }

        $this->writeMarkdown($path, $fields, $body ?? $existingBody);

        return $file;
    }

    /**
     * Changes the order of active components on a page.
     *
     * @param  array<int, string>  $orderedTypes
     */
    public function reorderComponents(Vendor $vendor, string $pageSlug, array $orderedTypes): void
    {
        $page = $this->readPage($vendor->slug, $pageSlug);
        $byType = [];

        foreach ($page['components'] ?? [] as $component) {
            $byType[$component['type']] = $component;
        }

        $reordered = [];
        foreach ($orderedTypes as $type) {
            if (! isset($byType[$type])) {
                throw new InvalidArgumentException("Component '{$type}' is not active on page '{$pageSlug}'.");
            }
            $reordered[] = $byType[$type];
        }

        $page['components'] = $reordered;
        $this->writeYaml("{$vendor->slug}/pages/{$pageSlug}.yaml", $page);
    }

    /**
     * Creates a new sub-page.
     */
    public function createSubpage(Vendor $vendor, string $pageSlug): void
    {
        $path = "{$vendor->slug}/pages/{$pageSlug}.yaml";

        if (Storage::disk($this->disk)->exists($path)) {
            throw new InvalidArgumentException("Sub-page already exists: {$pageSlug}");
        }

        $this->writeYaml($path, [
            'slug' => $pageSlug,
            'components' => [],
        ]);
    }

    /**
     * @return array{slug?:string, components?:array<int, array{type:string, file:string}>}
     */
    private function readPage(string $vendorSlug, string $pageSlug): array
    {
        $path = "{$vendorSlug}/pages/{$pageSlug}.yaml";

        if (! Storage::disk($this->disk)->exists($path)) {
            return ['slug' => $pageSlug, 'components' => []];
        }

        /** @var array<string, mixed> $parsed */
        $parsed = Yaml::parse(Storage::disk($this->disk)->get($path)) ?? [];

        return $parsed;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function writeYaml(string $path, array $data): void
    {
        $yaml = Yaml::dump($data, 4, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
        $this->atomicWrite($path, $yaml);
    }

    /**
     * @param  array<string, mixed>  $frontmatter
     */
    private function writeMarkdown(string $path, array $frontmatter, string $body): void
    {
        $yamlBlock = $frontmatter === []
            ? ''
            : trim(Yaml::dump($frontmatter, 4, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK));

        $content = "---\n{$yamlBlock}\n---\n\n".trim($body)."\n";
        $this->atomicWrite($path, $content);
    }

    private function atomicWrite(string $path, string $content): void
    {
        $tmp = $path.'.'.Str::random(8).'.tmp';
        Storage::disk($this->disk)->put($tmp, $content);

        if (! Storage::disk($this->disk)->move($tmp, $path)) {
            Storage::disk($this->disk)->delete($tmp);
            throw new RuntimeException("Failed to write atomically: {$path}");
        }
    }

    private function extractBody(string $raw): string
    {
        if (! str_starts_with($raw, '---')) {
            return trim($raw);
        }

        $parts = preg_split('/^---\s*$/m', $raw, 3);

        if ($parts === false || count($parts) < 3) {
            return '';
        }

        return trim($parts[2]);
    }

    /**
     * Checks all required fields against the component schema.
     *
     * @param  array<string, mixed>  $fields
     */
    private function assertSchemaCompliance(string $componentType, array $fields): void
    {
        $schemaPath = config_path("feedai/component-schemas/{$componentType}.yaml");

        if (! is_file($schemaPath)) {
            throw new InvalidArgumentException("Unknown component type: {$componentType}");
        }

        /** @var array{fields?: array<string, array{required?: bool}>} $schema */
        $schema = Yaml::parseFile($schemaPath);

        foreach ($schema['fields'] ?? [] as $name => $definition) {
            if (($definition['required'] ?? false) && empty($fields[$name])) {
                throw new InvalidArgumentException(
                    "Missing required field '{$name}' for component '{$componentType}'."
                );
            }
        }
    }
}
