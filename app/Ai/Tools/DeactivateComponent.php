<?php

namespace App\Ai\Tools;

use App\Models\Vendor;
use App\Services\ContentLoader;
use App\Services\ContentWriter;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * Removes a component from the components list of a page.
 *
 * The content file is preserved in case the component is
 * reactivated later.
 */
class DeactivateComponent implements Tool
{
    public function __construct(
        private readonly Vendor $vendor,
        private readonly ContentWriter $writer,
        private readonly ContentLoader $loader,
    ) {}

    public function description(): Stringable|string
    {
        return 'Deactivate a component on a page. Removes it from the page list but keeps the content '
            .'file for later reactivation.';
    }

    public function handle(Request $request): Stringable|string
    {
        $page = $request['page_slug'] ?? 'home';
        $type = $request['type'];

        $this->writer->deactivateComponent($this->vendor, $page, $type);

        return "Component '{$type}' deactivated on page '{$page}'.";
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'page_slug' => $schema->string()->description('The page slug. Defaults to "home".'),
            'type' => $schema->string()
                ->description('The component type to deactivate.')
                ->enum($this->loader->availableComponentTypes())
                ->required(),
        ];
    }
}
