<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Standard-Seeder für FeedAI.
 *
 * Setzt einen Admin (admin@feedai.test / password), den Demo-Vendor mit
 * Mae-Som-Storage-Anker (vendor@feedai.test / password), plus realistische
 * Demo-Conversations + Payments damit Admin/Inbox/Payment-Log sofort
 * etwas zu zeigen haben.
 *
 * Alles idempotent — Re-Seed im laufenden Dev überschreibt sauber.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            DemoVendorSeeder::class,
            DemoInboxSeeder::class,
            DemoPaymentsSeeder::class,
        ]);
    }
}
