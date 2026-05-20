<?php

namespace App\Console\Commands\Translations;

use App\Jobs\TranslateComponent;
use App\Models\Vendor;
use App\Services\ContentLoader;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature(<<<'SIG'
feedai:backfill-translations
    {vendor? : Slug of a single vendor — omit to process all live vendors}
    {--force-sync : Run TranslateComponent synchronously (slower, no queue worker needed)}
SIG)]
#[Description('Re-translate every active component for one or all vendors. Useful after schema changes or before a demo.')]
class BackfillVendorTranslations extends Command
{
    public function handle(ContentLoader $loader): int
    {
        $slug = $this->argument('vendor');

        $vendors = $slug
            ? Vendor::where('slug', $slug)->get()
            : Vendor::where('status', 'live')->get();

        if ($vendors->isEmpty()) {
            $this->warn('No vendors matched '.($slug ? "slug '{$slug}'" : 'status=live').'.');

            return self::FAILURE;
        }

        $forceSync = (bool) $this->option('force-sync');

        foreach ($vendors as $vendor) {
            $this->info("→ {$vendor->slug} (source: {$vendor->locale})");

            try {
                $page = $loader->loadPage($vendor->slug, 'home');
            } catch (\Throwable $e) {
                $this->error("  page load failed: {$e->getMessage()}");

                continue;
            }

            foreach ($page['components'] ?? [] as $component) {
                $type = $component['type'] ?? null;
                if (! $type) {
                    continue;
                }

                $this->line("  · {$type}");

                if ($forceSync) {
                    (new TranslateComponent($vendor->id, 'home', $type))->handle($loader);
                } else {
                    TranslateComponent::dispatch($vendor->id, 'home', $type);
                }
            }
        }

        $this->newLine();
        $this->info($forceSync
            ? 'All translations rewritten.'
            : 'Jobs dispatched. Make sure a queue worker is running.');

        return self::SUCCESS;
    }
}
