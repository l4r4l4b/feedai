<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Vendor>
 */
class VendorFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement([
            'Mae Som Pad Thai',
            'Bangkok Tuk-Tuk Tours',
            'Khao San Coffee',
            'Pranee Massage Studio',
            'Wat Pho Tour Guide',
        ]).' '.fake()->randomNumber(3);

        return [
            'user_id' => User::factory()->vendor(),
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'name' => $name,
            'status' => 'draft',
            'locale' => 'th',
            'onboarding_completed_at' => null,
        ];
    }

    public function live(): static
    {
        return $this->state(fn () => [
            'status' => 'live',
            'onboarding_completed_at' => now(),
        ]);
    }

    public function disabled(): static
    {
        return $this->state(fn () => ['status' => 'disabled']);
    }

    /**
     * Vendor mit komplett verkabeltem Stripe Connect Express Account,
     * bereit für Destination-Charge.
     */
    public function withStripeConnect(): static
    {
        return $this->state(fn () => [
            'stripe_account_id' => 'acct_test_'.fake()->bothify('??##??##??##'),
            'stripe_charges_enabled' => true,
            'stripe_details_submitted' => true,
        ]);
    }

    public function withPromptPay(): static
    {
        return $this->state(fn () => [
            'promptpay_phone' => '+66'.fake()->numerify('#########'),
        ]);
    }
}
