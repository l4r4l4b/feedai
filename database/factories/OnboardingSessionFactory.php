<?php

namespace Database\Factories;

use App\Models\OnboardingSession;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OnboardingSession>
 */
class OnboardingSessionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // conversation_id bleibt initial null — der OnboardingAgent setzt sie
        // beim ersten prompt(), wenn die AI-SDK eine Conversation erzeugt.
        return [
            'vendor_id' => Vendor::factory(),
            'conversation_id' => null,
            'status' => 'in_progress',
            'finalized_at' => null,
        ];
    }

    public function finalized(): self
    {
        return $this->state(fn () => [
            'status' => 'finalized',
            'finalized_at' => now(),
        ]);
    }

    public function abandoned(): self
    {
        return $this->state(fn () => ['status' => 'abandoned']);
    }
}
