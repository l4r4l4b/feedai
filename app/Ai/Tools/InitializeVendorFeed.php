<?php

namespace App\Ai\Tools;

use App\Models\Vendor;
use App\Services\ContentWriter;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * Initialisiert das Vendor-Storage-Skelett und übernimmt Grundinfos
 * aus dem Onboarding-Chat in die Vendor-DB-Row.
 *
 * Muss vor allen anderen Tools aufgerufen werden.
 */
class InitializeVendorFeed implements Tool
{
    public function __construct(
        private readonly Vendor $vendor,
        private readonly ContentWriter $writer,
    ) {}

    public function description(): Stringable|string
    {
        return 'Initialize the vendor feed storage skeleton with basic info (business name and locale). '
            .'Must be called first, before activating or filling any components.';
    }

    public function handle(Request $request): Stringable|string
    {
        $this->vendor->forceFill([
            'name' => $request['name'],
            'locale' => $request['locale'] ?? 'th',
        ])->save();

        $this->writer->initializeVendor($this->vendor->fresh());

        return sprintf(
            'Vendor "%s" initialized with locale "%s". Empty home page created. Ready for activateComponent calls.',
            $this->vendor->name,
            $this->vendor->locale,
        );
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()
                ->description('The business / vendor name as it should appear publicly.')
                ->required(),
            'locale' => $schema->string()
                ->description('Vendor working language. Use "th" for Thai, "en" for English, "de" for German.')
                ->enum(['th', 'en', 'de']),
        ];
    }
}
