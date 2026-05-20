<?php

namespace App\Models;

use Database\Factories\VendorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Fillable([
    'user_id',
    'slug',
    'name',
    'status',
    'locale',
    'onboarding_completed_at',
    'stripe_account_id',
    'stripe_charges_enabled',
    'stripe_details_submitted',
    'promptpay_phone',
    'stablecoin_address',
    'stablecoin_chain',
])]
class Vendor extends Model implements HasMedia
{
    /** @use HasFactory<VendorFactory> */
    use HasFactory, InteractsWithMedia;

    protected function casts(): array
    {
        return [
            'onboarding_completed_at' => 'datetime',
            'stripe_charges_enabled' => 'boolean',
            'stripe_details_submitted' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Whether this vendor can accept card payments — i.e. completed the
     * Stripe Connect Express onboarding so charges can be routed via
     * Destination Charge to their account.
     */
    public function acceptsCards(): bool
    {
        return filled($this->stripe_account_id) && $this->stripe_charges_enabled;
    }

    public function acceptsPromptPay(): bool
    {
        return filled($this->promptpay_phone);
    }

    public function acceptsStablecoin(): bool
    {
        return filled($this->stablecoin_address) && filled($this->stablecoin_chain);
    }
}
