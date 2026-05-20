<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vendor_id' => Vendor::factory(),
            'amount_cents' => fake()->numberBetween(5000, 250000),
            'currency' => 'THB',
            'method' => 'stripe',
            'status' => 'pending',
            'provider_reference' => null,
            'description' => fake()->randomElement([
                'Tour: Damnoen Saduak Floating Market',
                'Pad Thai + Drink',
                'Massage 60 min',
                'Tip',
            ]),
            'metadata' => null,
            'paid_at' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => 'paid',
            'paid_at' => now(),
            'provider_reference' => 'cs_test_'.fake()->bothify('??##??##??##'),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => ['status' => 'failed']);
    }

    public function promptpay(): static
    {
        return $this->state(fn () => ['method' => 'promptpay']);
    }

    public function stablecoin(): static
    {
        return $this->state(fn () => ['method' => 'stablecoin', 'currency' => 'USDC']);
    }
}
