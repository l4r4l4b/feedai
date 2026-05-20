<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;

/**
 * Reads vendor content from storage/app/vendors/{slug}/.
 *
 * Three layers:
 *  - Vendor metadata (vendor.yaml)
 *  - Page definition (pages/{page}.yaml) — active components + order
 *  - Component content (content/{page}/{file}.md) — frontmatter + body
 *
 * Schemas live under config/feedai/component-schemas/{type}.yaml and are
 * used for required-field validation.
 */
class ContentLoader
{
    public function __construct(private readonly string $disk = 'vendors') {}

    /**
     * @return array{slug:string, name:string, template:string, locale:string, status:string, accent_color:?string}
     */
    public function loadVendor(string $vendorSlug): array
    {
        $path = "{$vendorSlug}/vendor.yaml";

        if (! Storage::disk($this->disk)->exists($path)) {
            throw new RuntimeException("Vendor not found: {$vendorSlug}");
        }

        /** @var array<string, mixed> $data */
        $data = Yaml::parse(Storage::disk($this->disk)->get($path));

        return [
            'slug' => $data['slug'] ?? $vendorSlug,
            'name' => $data['name'] ?? '',
            'template' => $data['template'] ?? 'default',
            'locale' => $data['locale'] ?? 'th',
            'status' => $data['status'] ?? 'draft',
            'accent_color' => $data['accent_color'] ?? null,
        ];
    }

    /**
     * @return array{slug:string, components:array<int, array{type:string, file:string}>}
     */
    public function loadPage(string $vendorSlug, string $pageSlug): array
    {
        $path = "{$vendorSlug}/pages/{$pageSlug}.yaml";

        if (! Storage::disk($this->disk)->exists($path)) {
            throw new RuntimeException("Page not found: {$vendorSlug}/{$pageSlug}");
        }

        /** @var array<string, mixed> $data */
        $data = Yaml::parse(Storage::disk($this->disk)->get($path));

        return [
            'slug' => $data['slug'] ?? $pageSlug,
            'components' => $data['components'] ?? [],
        ];
    }

    /**
     * Loads a single component file and validates required fields.
     *
     * @return array{type:string, fields:array<string, mixed>, body:string}
     */
    public function loadComponent(string $vendorSlug, string $pageSlug, string $componentType, string $file): array
    {
        $path = "{$vendorSlug}/content/{$pageSlug}/{$file}";

        if (! Storage::disk($this->disk)->exists($path)) {
            throw new RuntimeException("Component file not found: {$path}");
        }

        $raw = Storage::disk($this->disk)->get($path);
        [$fields, $body] = $this->parseFrontmatter($raw);

        $this->assertRequiredFields($componentType, $fields);

        return [
            'type' => $componentType,
            'fields' => $this->camelCaseKeys($fields),
            'body' => $body,
        ];
    }

    /**
     * @param  array<string, mixed>  $fields
     * @return array<string, mixed>
     */
    private function camelCaseKeys(array $fields): array
    {
        $out = [];

        foreach ($fields as $key => $value) {
            $out[Str::camel($key)] = $value;
        }

        return $out;
    }

    /**
     * Loads an entire page including all component contents.
     *
     * @return array<int, array{type:string, fields:array<string, mixed>, body:string}>
     */
    public function loadPageComponents(string $vendorSlug, string $pageSlug): array
    {
        $page = $this->loadPage($vendorSlug, $pageSlug);

        return array_map(
            fn (array $component): array => $this->loadComponent(
                $vendorSlug,
                $pageSlug,
                $component['type'],
                $component['file'],
            ),
            $page['components'],
        );
    }

    /**
     * List of all available component types (from the schema files).
     *
     * @return array<int, string>
     */
    public function availableComponentTypes(): array
    {
        $files = glob(config_path('feedai/component-schemas/*.yaml')) ?: [];

        $types = array_map(
            fn (string $path): string => basename($path, '.yaml'),
            $files,
        );

        sort($types);

        return $types;
    }

    /**
     * @return array{0: array<string, mixed>, 1: string}
     */
    private function parseFrontmatter(string $raw): array
    {
        if (! str_starts_with($raw, '---')) {
            return [[], trim($raw)];
        }

        $parts = preg_split('/^---\s*$/m', $raw, 3);

        if ($parts === false || count($parts) < 3) {
            return [[], trim($raw)];
        }

        /** @var array<string, mixed> $fields */
        $fields = Yaml::parse(trim($parts[1])) ?? [];

        return [$fields, trim($parts[2])];
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    private function assertRequiredFields(string $componentType, array $fields): void
    {
        $schemaPath = config_path("feedai/component-schemas/{$componentType}.yaml");

        if (! is_file($schemaPath)) {
            throw new RuntimeException("Schema not found for component: {$componentType}");
        }

        /** @var array{fields?: array<string, array{required?: bool}>} $schema */
        $schema = Yaml::parseFile($schemaPath);

        foreach ($schema['fields'] ?? [] as $name => $definition) {
            if (($definition['required'] ?? false) && empty($fields[$name])) {
                throw new InvalidArgumentException(
                    "Missing required field '{$name}' for component '{$componentType}'"
                );
            }
        }
    }
}
