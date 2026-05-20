<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            // Stripe Connect Express — Karten-Zahlung via Destination Charge.
            // FeedAI hält das Geld nie; Stripe leitet direkt an den Express-Account weiter.
            $table->string('stripe_account_id')->nullable()->unique()->after('locale');
            $table->boolean('stripe_charges_enabled')->default(false)->after('stripe_account_id');
            $table->boolean('stripe_details_submitted')->default(false)->after('stripe_charges_enabled');

            // Class-A Direct-Payment — wir zeigen nur an, kein Geldfluss durch uns.
            $table->string('promptpay_phone')->nullable()->after('stripe_details_submitted');
            $table->string('stablecoin_address')->nullable()->after('promptpay_phone');
            $table->string('stablecoin_chain', 16)->nullable()->after('stablecoin_address');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_account_id',
                'stripe_charges_enabled',
                'stripe_details_submitted',
                'promptpay_phone',
                'stablecoin_address',
                'stablecoin_chain',
            ]);
        });
    }
};
