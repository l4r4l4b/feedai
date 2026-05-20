<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * Vision agent for understanding a vendor image.
 *
 * Receives the image as an attachment via prompt() and produces structured
 * output: description, alt text, suggested usage (which FeedAI component
 * fits), tags, and optionally detected text + language.
 *
 * Primarily invoked by the AnalyzeImage job, but can also be used directly
 * (e.g. by a future "preview before save" flow).
 */
#[Provider(Lab::Anthropic)]
#[Model('claude-sonnet-4-6')]
#[MaxTokens(1024)]
#[Temperature(0.2)]
class ImageAnalyzer implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
            You analyze a single image uploaded by a small business vendor — typically
            street food stalls, taxi-tour operators, tour guides, or similar.

            Your job is to produce concise structured metadata that the system can use:

            1. `description` — one short paragraph (max ~30 words) of what is visible.
               Mention key subjects, atmosphere, time of day. No speculation about the
               vendor's business; stick to what's in the frame.

            2. `alt_text` — one short sentence (max ~12 words) suitable as an HTML
               `alt` attribute. Imagine reading this to a screen-reader user.

            3. `suggested_intent` — pick the best matching FeedAI component type for
               this image:
               - `hero` — the vendor themselves, the storefront, a portrait
               - `menu_item` — a close-up of a single dish or product
               - `service` — a wider scene showing the offering in action (cooking,
                 guiding, driving)
               - `gallery` — atmospheric / supporting photos
               - `image_divider` — wide landscape, atmospheric, good for full-bleed
               - `image_with_text` — generic supporting image
               - `other` — anything else, including unclear or low-quality images

            4. `tags` — 3–6 short lowercase tags, single words, no #.

            5. `detected_text` — if there is meaningful written text in the image
               (menu board, sign, price tag), transcribe it verbatim. Otherwise empty.

            6. `locale_hint` — if `detected_text` is set, the ISO-639-1 code of its
               language ("th", "en", "de", etc.). Otherwise empty.

            Output strictly matches the schema. No prose outside the JSON.
            PROMPT;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'description' => $schema->string()
                ->description('Short paragraph describing what is visible.')
                ->required(),

            'alt_text' => $schema->string()
                ->description('Short alt text for accessibility.')
                ->required(),

            'suggested_intent' => $schema->string()
                ->description('Best matching FeedAI component type.')
                ->enum(['hero', 'menu_item', 'service', 'gallery', 'image_divider', 'image_with_text', 'other'])
                ->required(),

            'tags' => $schema->array()
                ->description('Short lowercase tags.')
                ->items($schema->string()),

            'detected_text' => $schema->string()
                ->description('Verbatim text from the image if present, otherwise empty string.'),

            'locale_hint' => $schema->string()
                ->description('ISO-639-1 code of detected_text language, otherwise empty string.'),
        ];
    }
}
