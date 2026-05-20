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
 * Aktiviert eine Komponente auf einer Page. Idempotent.
 *
 * Nach Activate ist die Komponente in pages/{page}.yaml gelistet,
 * aber noch ohne Content. fillComponent muss folgen.
 */
class ActivateComponent implements Tool
{
    public function __construct(
        private readonly Vendor $vendor,
        private readonly ContentWriter $writer,
        private readonly ContentLoader $loader,
    ) {}

    public function description(): Stringable|string
    {
        return 'Activate a component on a page. The component will be added to the page in declaration '
            .'order. Use this before fillComponent. Calling twice on the same type is safe.';
    }

    public function handle(Request $request): Stringable|string
    {
        $page = $request['page_slug'] ?? 'home';
        $type = $request['type'];

        $file = $this->writer->activateComponent($this->vendor, $page, $type);

        return "Component '{$type}' activated on page '{$page}' (file: {$file}). Call fillComponent next.";
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'page_slug' => $schema->string()
                ->description('The page slug. Defaults to "home".'),
            'type' => $schema->string()
                ->description('The component type to activate.')
                ->enum($this->loader->availableComponentTypes())
                ->required(),
        ];
    }
}
