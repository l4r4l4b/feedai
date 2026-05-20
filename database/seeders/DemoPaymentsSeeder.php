<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

/**
 * Demo-Payments für den Admin-Payment-Log. Mischung aus paid / pending /
 * refunded und allen drei Methoden (stripe / promptpay / stablecoin), damit
 * die Tabellen-States im Admin sichtbar werden.
 */
class DemoPaymentsSeeder extends Seeder
{
    public function run(): void
    {
        $vendor = Vendor::where('slug', 'demo')->first();

        if (! $vendor) {
            return;
        }

        $payments = [
            [
                'amount_cents' => 8000,
                'currency' => 'THB',
                'method' => 'stripe',
                'status' => 'paid',
                'provider_reference' => 'pi_demo_1',
                'description' => 'Pad Thai Goong x1',
                'paid_at' => now()->subHours(2),
            ],
            [
                'amount_cents' => 12000,
                'currency' => 'THB',
                'method' => 'stripe',
                'status' => 'paid',
                'provider_reference' => 'pi_demo_2',
                'description' => 'Custom Tip',
                'paid_at' => now()->subHours(5),
            ],
            [
                'amount_cents' => 6000,
                'currency' => 'THB',
                'method' => 'promptpay',
                'status' => 'paid',
                'provider_reference' => 'pp_demo_1',
                'description' => 'Pad Thai Tofu x1',
                'paid_at' => now()->subDay(),
            ],
            [
                'amount_cents' => 120000,
                'currency' => 'THB',
                'method' => 'stripe',
                'status' => 'pending',
                'provider_reference' => 'cs_demo_3',
                'description' => 'Cooking Class — Anzahlung',
                'paid_at' => null,
            ],
            [
                'amount_cents' => 4500,
                'currency' => 'THB',
                'method' => 'stablecoin',
                'status' => 'paid',
                'provider_reference' => '0xabc123…demo',
                'description' => 'Pad See Ew',
                'paid_at' => now()->subDays(2),
            ],
            [
                'amount_cents' => 7000,
                'currency' => 'THB',
                'method' => 'stripe',
                'status' => 'refunded',
                'provider_reference' => 'pi_demo_refund',
                'description' => 'Erstattung — falscher Betrag',
                'paid_at' => now()->subDays(3),
            ],
        ];

        foreach ($payments as $payload) {
            Payment::updateOrCreate(
                [
                    'vendor_id' => $vendor->id,
                    'provider_reference' => $payload['provider_reference'],
                ],
                array_merge($payload, ['vendor_id' => $vendor->id]),
            );
        }
    }
}
