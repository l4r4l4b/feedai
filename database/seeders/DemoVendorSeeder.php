<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoVendorSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'vendor@feedai.test'],
            [
                'name' => 'Mae Som',
                'password' => Hash::make('password'),
                'role' => 'vendor',
                'email_verified_at' => now(),
            ],
        );

        // Demo-Vendor: Stripe Connect Account ID + (optionale) PromptPay + Crypto
        // werden aus .env gezogen, damit der Seeder rein DB-only läuft und keinen
        // Live-Stripe-Call macht. Wenn STRIPE_DEMO_ACCOUNT_ID gesetzt ist,
        // gilt der Vendor als kartenbereit für die Demo.
        $stripeAccountId = env('STRIPE_DEMO_ACCOUNT_ID');

        // Mirror what storage/app/vendors/demo/vendor.yaml says. Slug 'demo'
        // is the anchor referenced by the public feed renderer and the
        // PublicFeedTest. status=live so the Admin-Liste later shows it green.
        Vendor::updateOrCreate(
            ['slug' => 'demo'],
            [
                'user_id' => $user->id,
                'name' => 'Mae Som Pad Thai',
                'status' => 'live',
                'locale' => 'th',
                'onboarding_completed_at' => now(),
                'stripe_account_id' => $stripeAccountId,
                'stripe_charges_enabled' => filled($stripeAccountId),
                'stripe_details_submitted' => filled($stripeAccountId),
                'promptpay_phone' => env('DEMO_PROMPTPAY_PHONE', '0812345678'),
                'stablecoin_address' => env('DEMO_STABLECOIN_ADDRESS', '0x742d35Cc6634C0532925a3b844Bc454e4438f44e'),
                'stablecoin_chain' => env('DEMO_STABLECOIN_CHAIN', 'ETH'),
            ],
        );
    }
}
