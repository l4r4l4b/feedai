<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'sender' => 'tourist',
            'original_text' => fake()->sentence(),
            'original_locale' => 'en',
            'translated_text' => null,
            'translated_locale' => null,
            'read_at' => null,
        ];
    }

    public function fromTourist(): static
    {
        return $this->state(fn () => [
            'sender' => 'tourist',
            'original_locale' => fake()->randomElement(['en', 'de']),
        ]);
    }

    public function fromVendor(): static
    {
        return $this->state(fn () => [
            'sender' => 'vendor',
            'original_locale' => 'th',
        ]);
    }

    public function translated(): static
    {
        return $this->state(fn (array $attrs) => [
            'translated_text' => fake()->sentence(),
            'translated_locale' => $attrs['original_locale'] === 'th' ? 'en' : 'th',
        ]);
    }

    public function read(): static
    {
        return $this->state(fn () => ['read_at' => now()]);
    }
}
