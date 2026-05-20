<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Conversation>
 */
class ConversationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vendor_id' => Vendor::factory(),
            'tourist_locale' => fake()->randomElement(['en', 'de']),
            'tourist_name' => fake()->name(),
            'tourist_email' => fake()->safeEmail(),
            'last_message_at' => now(),
            // token is auto-filled by Conversation::booted()
        ];
    }

    public function fromGermanTourist(): static
    {
        return $this->state(fn () => ['tourist_locale' => 'de']);
    }

    public function fromEnglishTourist(): static
    {
        return $this->state(fn () => ['tourist_locale' => 'en']);
    }
}
