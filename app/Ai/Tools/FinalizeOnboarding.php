<?php

namespace App\Ai\Tools;

use App\Models\OnboardingSession;
use App\Models\Vendor;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * Schließt das Onboarding ab. Vendor wird live geschaltet.
 *
 * Darf nur aufgerufen werden wenn mindestens Hero + About + ein Kontakt-
 * Channel aktiv sind (Soft-Validation im System-Prompt, nicht hier).
 */
class FinalizeOnboarding implements Tool
{
    public function __construct(
        private readonly Vendor $vendor,
    ) {}

    public function description(): Stringable|string
    {
        return 'Finalize the vendor onboarding. Sets the vendor status to live and marks the onboarding '
            .'session as finalized. Only call when all essential components (hero, contact) are filled.';
    }

    public function handle(Request $request): Stringable|string
    {
        $now = now();

        $this->vendor->forceFill([
            'status' => 'live',
            'onboarding_completed_at' => $now,
        ])->save();

        OnboardingSession::where('vendor_id', $this->vendor->id)
            ->where('status', 'in_progress')
            ->update([
                'status' => 'finalized',
                'finalized_at' => $now,
            ]);

        return sprintf(
            'Onboarding finalized. Vendor "%s" is now LIVE at /%s.',
            $this->vendor->name,
            $this->vendor->slug,
        );
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
