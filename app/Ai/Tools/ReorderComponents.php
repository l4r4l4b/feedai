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
 * Changes the order of the active components on a page.
 *
 * The list provided must contain exactly the currently active types —
 * none missing, none extra.
 */
class ReorderComponents implements Tool
{
    public function __construct(
        private readonly Vendor $vendor,
        private readonly ContentWriter $writer,
        private readonly ContentLoader $loader,
    ) {}

    public function description(): Stringable|string
    {
        return 'Reorder the active components on a page. Provide the component types in the desired new order. '
            .'The list must include every currently active type on the page.';
    }

    public function handle(Request $request): Stringable|string
    {
        $page = $request['page_slug'] ?? 'home';
        $types = $request['types'];

        $this->writer->reorderComponents($this->vendor, $page, $types);

        $order = implode(' → ', $types);

        return "Page '{$page}' reordered: {$order}.";
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'page_slug' => $schema->string()->description('The page slug. Defaults to "home".'),
            'types' => $schema->array()
                ->description('Active component types in the desired order. Must match the currently active set exactly.')
                ->items(
                    $schema->string()->enum($this->loader->availableComponentTypes())
                )
                ->required(),
        ];
    }
}
