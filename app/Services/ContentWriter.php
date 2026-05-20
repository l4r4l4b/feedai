<?php

namespace App\Services;

use App\Models\Vendor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;

/**
 * Schreibt Vendor-Content nach storage/app/vendors/{slug}/.
 *
 * Pendant zu ContentLoader. Wird von AI-Tools und Direct-Edit-Drawer
 * benutzt. Schreibt atomar (tmp-file + rename) damit Reader nie auf
 * halb geschriebene Files treffen.
 *
 * Snake-case Frontmatter-Keys bleiben in YAML/MD erhalten (Konvention).
 * Die camelCase-Transformation passiert erst beim Lesen über ContentLoader.
 */
class ContentWriter
{
    public function __construct(private readonly string $disk = 'vendors') {}

    /**
     * Legt das initiale Storage-Skelett für einen neuen Vendor an.
     *
     * Erzeugt vendor.yaml + leere pages/home.yaml mit Template-Referenz.
     * Keine Komponenten aktiv — AI aktiviert sie schrittweise.
     */
    public function initializeVendor(Vendor $vendor, string $template = 'default'): void
    {
        $slug = $vendor->slug;

        // Metadaten immer aktualisieren — AI darf Name/Locale/Accent später ändern.
        $this->writeYaml("{$slug}/vendor.yaml", [
            'slug' => $vendor->slug,
            'name' => $vendor->name,
            'template' => $template,
            'locale' => $vendor->locale,
            'status' => $vendor->status,
            'accent_color' => $vendor->accent_color,
        ]);

        // Home-Page nur anlegen falls noch nicht da — sonst würden bereits
        // aktivierte Komponenten beim erneuten Init verloren gehen.
        $homePath = "{$slug}/pages/home.yaml";
        if (! Storage::disk($this->disk)->exists($homePath)) {
            $this->writeYaml($homePath, [
                'slug' => 'home',
                'components' => [],
            ]);
        }
    }

    /**
     * Aktiviert eine Komponente auf einer Page (legt sie zur Components-Liste).
     *
     * Idempotent — doppeltes Aktivieren wirft nicht. Datei wird angelegt
     * sofern noch nicht da (mit leerer Frontmatter).
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
     * Deaktiviert eine Komponente — entfernt aus Liste, Content-File bleibt erhalten.
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
     * Befüllt eine Komponente mit Daten. Validiert gegen Schema.
     *
     * Bestehender Body wird übernommen wenn kein neuer übergeben wird.
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
     * Ändert Reihenfolge der aktiven Komponenten auf einer Page.
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
     * Legt eine neue Sub-Page an.
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
     * Prüft alle required-Felder gegen das Component-Schema.
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
