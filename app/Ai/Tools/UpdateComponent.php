<?php

namespace App\Ai\Tools;

use App\Jobs\TranslateComponent;
use App\Models\Vendor;
use App\Services\ContentLoader;
use App\Services\ContentWriter;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use InvalidArgumentException;
use JsonException;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * Updates a component that has already been filled. Functionally identical
 * to FillComponent — a semantic alias so the agent can distinguish between
 * "initial fill" and "edit".
 */
class UpdateComponent implements Tool
{
    public function __construct(
        private readonly Vendor $vendor,
        private readonly ContentWriter $writer,
        private readonly ContentLoader $loader,
    ) {}

    public function description(): Stringable|string
    {
        return 'Update an existing component with new field values. Overwrites the component frontmatter '
            .'and optionally the body. Triggers retranslation.';
    }

    public function handle(Request $request): Stringable|string
    {
        $page = $request['page_slug'] ?? 'home';
        $type = $request['type'];
        $body = $request['body'] ?? null;

        try {
            $fields = json_decode($request['fields'], true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidArgumentException("fields must be valid JSON object string: {$e->getMessage()}");
        }

        if (! is_array($fields)) {
            throw new InvalidArgumentException('fields must decode to a JSON object.');
        }

        $this->writer->fillComponent($this->vendor, $page, $type, $fields, $body);

        TranslateComponent::dispatch($this->vendor->id, $page, $type);

        return "Component '{$type}' updated on page '{$page}'. Retranslation dispatched.";
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'page_slug' => $schema->string()->description('The page slug. Defaults to "home".'),
            'type' => $schema->string()
                ->description('The component type to update.')
                ->enum($this->loader->availableComponentTypes())
                ->required(),
            'fields' => $schema->string()
                ->description('JSON object string with the new field values. Same shape as in fillComponent.')
                ->required(),
            'body' => $schema->string()
                ->description('Optional new Markdown body. Omit to keep the existing body.'),
        ];
    }
}
