<?php

namespace App\Jobs;

use App\Ai\Agents\Translator;
use App\Models\Vendor;
use App\Services\ContentLoader;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Übersetzt eine einzelne Komponente in alle Tourist-Sprachen.
 *
 * Liest das Original-File aus content/{page}/{file} und schreibt
 * pro Ziel-Locale ein paralleles File nach translations/{lang}/{page}/{file}.
 *
 * Dedupliziert per Vendor+Page+Type via ShouldBeUnique, damit parallele
 * Edits keine Race-Conditions auf dem Translation-File auslösen.
 */
class TranslateComponent implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Ziel-Locales — Source-Locale wird automatisch übersprungen.
     *
     * @var list<string>
     */
    private const TARGET_LOCALES = ['en', 'de'];

    public function __construct(
        public readonly int $vendorId,
        public readonly string $pageSlug,
        public readonly string $componentType,
    ) {}

    public function uniqueId(): string
    {
        return "translate:{$this->vendorId}:{$this->pageSlug}:{$this->componentType}";
    }

    public function uniqueFor(): int
    {
        return 60;
    }

    public function handle(ContentLoader $loader): void
    {
        $vendor = Vendor::find($this->vendorId);

        if (! $vendor) {
            return;
        }

        $componentFile = $this->resolveComponentFile($loader, $vendor->slug);

        if ($componentFile === null) {
            return;
        }

        $disk = Storage::disk('vendors');
        $sourcePath = "{$vendor->slug}/content/{$this->pageSlug}/{$componentFile}";

        if (! $disk->exists($sourcePath)) {
            return;
        }

        $sourceContent = $disk->get($sourcePath);
        $sourceLocale = $vendor->locale ?? 'th';

        foreach (self::TARGET_LOCALES as $target) {
            if ($target === $sourceLocale) {
                continue;
            }

            try {
                $translated = (new Translator($target))->prompt($sourceContent);
            } catch (\Throwable $e) {
                Log::warning('Translation failed', [
                    'vendor' => $vendor->slug,
                    'page' => $this->pageSlug,
                    'type' => $this->componentType,
                    'target' => $target,
                    'error' => $e->getMessage(),
                ]);

                continue;
            }

            $targetPath = "{$vendor->slug}/translations/{$target}/{$this->pageSlug}/{$componentFile}";
            $disk->put($targetPath, (string) $translated);
        }
    }

    /**
     * Look up the file name of the component on the page from page.yaml.
     */
    private function resolveComponentFile(ContentLoader $loader, string $vendorSlug): ?string
    {
        try {
            $page = $loader->loadPage($vendorSlug, $this->pageSlug);
        } catch (\Throwable) {
            return null;
        }

        foreach ($page['components'] ?? [] as $component) {
            if (($component['type'] ?? null) === $this->componentType) {
                return $component['file'] ?? null;
            }
        }

        return null;
    }
}
