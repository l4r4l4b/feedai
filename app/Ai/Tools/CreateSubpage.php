<?php

namespace App\Ai\Tools;

use App\Models\Vendor;
use App\Services\ContentWriter;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * Legt eine neue Unterseite an (z.B. "tour-floating-market").
 *
 * Unterseiten teilen sich die Komponenten-Bibliothek mit der Home-Page.
 */
class CreateSubpage implements Tool
{
    public function __construct(
        private readonly Vendor $vendor,
        private readonly ContentWriter $writer,
    ) {}

    public function description(): Stringable|string
    {
        return 'Create a new sub-page for the vendor (e.g. a specific tour or service detail page). '
            .'Use lowercase kebab-case slugs.';
    }

    public function handle(Request $request): Stringable|string
    {
        $slug = $request['page_slug'];

        $this->writer->createSubpage($this->vendor, $slug);

        return "Sub-page '{$slug}' created. It has no components yet — use activateComponent + fillComponent to populate.";
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'page_slug' => $schema->string()
                ->description('Lowercase kebab-case slug for the new page, e.g. "tour-floating-market".')
                ->pattern('^[a-z0-9][a-z0-9-]*$')
                ->required(),
        ];
    }
}
