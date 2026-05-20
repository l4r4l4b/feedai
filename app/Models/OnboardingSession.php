<?php

namespace App\Models;

use Database\Factories\OnboardingSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vendor_id', 'conversation_id', 'status', 'finalized_at'])]
class OnboardingSession extends Model
{
    /** @use HasFactory<OnboardingSessionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'finalized_at' => 'datetime',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isFinalized(): bool
    {
        return $this->status === 'finalized';
    }
}
