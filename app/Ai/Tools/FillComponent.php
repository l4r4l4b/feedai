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
 * Fills a component with content. Activates it if not already active.
 *
 * Subsequently triggers TranslateComponent for EN/DE translation.
 *
 * The fields argument is a JSON object string whose keys must match
 * the schema of the respective component type exactly
 * (see system prompt for the schemas).
 */
class FillComponent implements Tool
{
    public function __construct(
        private readonly Vendor $vendor,
        private readonly ContentWriter $writer,
        private readonly ContentLoader $loader,
    ) {}

    public function description(): Stringable|string
    {
        return 'Fill a component with content. Activates the component if not already active. '
            .'The "fields" argument is a JSON object string matching the component schema (snake_case keys). '
            .'The "body" argument is optional Markdown text for components that support it (about, text_block, image_with_text). '
            .'Triggers automatic translation to other locales.';
    }

    public function handle(Request $request): Stringable|string
    {
        $page = $request['page_slug'] ?? 'home';
        $type = $request['type'];
        $body = $request['body'] ?? null;

        $fields = $this->decodeFields($request['fields']);

        $this->writer->fillComponent($this->vendor, $page, $type, $fields, $body);

        TranslateComponent::dispatch($this->vendor->id, $page, $type);

        return "Component '{$type}' filled on page '{$page}'. Translation dispatched.";
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'page_slug' => $schema->string()
                ->description('The page slug. Defaults to "home".'),
            'type' => $schema->string()
                ->description('The component type to fill.')
                ->enum($this->loader->availableComponentTypes())
                ->required(),
            'fields' => $schema->string()
                ->description('JSON object string with the component fields, e.g. \'{"title": "...", "image": "..."}\'. Keys must use snake_case and match the schema for this component type.')
                ->required(),
            'body' => $schema->string()
                ->description('Optional Markdown body. Only relevant for about, text_block, image_with_text.'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeFields(string $raw): array
    {
        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidArgumentException("fields must be a valid JSON object string. Parse error: {$e->getMessage()}");
        }

        if (! is_array($decoded)) {
            throw new InvalidArgumentException('fields must decode to a JSON object, not a scalar or array.');
        }

        return $decoded;
    }
}
