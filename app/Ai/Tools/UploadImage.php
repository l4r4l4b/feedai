<?php

namespace App\Ai\Tools;

use App\Models\Vendor;
use App\Services\VendorImageIngestor;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * Tool für den Fall, dass der AI-Agent ein Bild aus rohem Base64-Material
 * direkt aufnehmen soll. In der Praxis ist das selten — die Livewire-Chat
 * UI persistiert Bilder schon vor dem Agent-Call über VendorImageIngestor.
 *
 * Wir behalten das Tool für Edge-Cases (z.B. AI generiert/transformiert
 * irgendwann selbst ein Bild) und routen es konsistent über denselben
 * Ingestor — eine Stelle für alle Vendor-Bild-Persistierungen.
 */
class UploadImage implements Tool
{
    public function __construct(
        private readonly Vendor $vendor,
        private readonly VendorImageIngestor $ingestor,
    ) {}

    public function description(): Stringable|string
    {
        return 'Persist a base64-encoded image on the vendor and return its public URL plus '
            .'an AI-generated description and suggested intent. Prefer to inspect the returned '
            .'description before deciding which component to use the image for.';
    }

    public function handle(Request $request): Stringable|string
    {
        $media = $this->ingestor->fromBase64(
            vendor: $this->vendor,
            base64: $request['data'],
            mime: $request['mime_type'],
            intent: $request['intended_use'] ?? null,
            source: 'agent-tool',
            analyzeSync: true,
        );

        $description = (string) $media->getCustomProperty('description', '');
        $suggested = (string) $media->getCustomProperty('suggested_intent', 'other');

        return sprintf(
            "url=%s\nsuggested_intent=%s\ndescription=%s",
            $media->getUrl(),
            $suggested,
            $description,
        );
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'data' => $schema->string()
                ->description('Base64-encoded binary image data (without data: prefix).')
                ->required(),
            'mime_type' => $schema->string()
                ->description('MIME type of the image.')
                ->enum(['image/jpeg', 'image/png', 'image/webp'])
                ->required(),
            'intended_use' => $schema->string()
                ->description('Optional intent hint, e.g. "hero", "menu-pad-thai", "gallery-1". '
                    .'The vision analyzer may suggest a different intent — trust its output.')
                ->required(),
        ];
    }
}
