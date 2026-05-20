<?php

namespace Database\Factories;

use App\Models\OnboardingSession;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        return [
            'vendor_id' => Vendor::factory(),
            'conversation_id' => (string) Str::uuid(),
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
