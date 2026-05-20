<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

/**
 * Standard-Seeder für FeedAI.
 *
 * Setzt einen Admin (admin@feedai.test / password), den Demo-Vendor mit
 * Mae-Som-Storage-Anker (vendor@feedai.test / password), plus realistische
 * Demo-Conversations + Payments damit Admin/Inbox/Payment-Log sofort
 * etwas zu zeigen haben.
 *
 * Alles idempotent — Re-Seed im laufenden Dev überschreibt sauber.
 *
 * Defense-in-depth: stellt sicher dass der storage:link existiert, damit
 * Spatie-Media-URLs ausgeliefert werden. Ohne den Symlink → 403 Forbidden
 * auf hochgeladene Bilder.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->ensureStorageLink();

        $this->call([
            AdminUserSeeder::class,
            DemoVendorSeeder::class,
            DemoInboxSeeder::class,
            DemoPaymentsSeeder::class,
        ]);
    }

    /**
     * Symlink public/storage → storage/app/public ist Voraussetzung dafür,
     * dass Spatie-Media-URLs (`/storage/{id}/file.webp`) ausgeliefert werden.
     * Artisan-Command ist idempotent — wenn der Link existiert, passiert nichts.
     */
    private function ensureStorageLink(): void
    {
        if (file_exists(public_path('storage'))) {
            return;
        }

        Artisan::call('storage:link');
    }
}
