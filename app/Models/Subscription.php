<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Subscription extends Model
{
    protected $fillable = [
        'organization_id',
        'plan_name',
        'status',
        'monthly_price',
        'max_members',
        'max_projects',
        'max_storage_gb',
        'current_period_start',
        'current_period_end',
        'next_billing_date',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'next_billing_date' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function pricingPlan(): BelongsTo
    {
        return $this->belongsTo(PricingPlan::class, 'plan_name', 'slug');
    }

    public function billingHistories(): HasMany
    {
        return $this->hasMany(BillingHistory::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || 
               ($this->current_period_end && $this->current_period_end->isPast());
    }

    public function getDaysUntilBilling(): int
    {
        if (!$this->next_billing_date) {
            return 0;
        }
        
        return Carbon::now()->diffInDays($this->next_billing_date, false);
    }

    public function getUsagePercentage(string $type, int $current): float
    {
        $max = match($type) {
            'members' => $this->max_members,
            'projects' => $this->max_projects,
            'storage' => $this->max_storage_gb,
            default => null
        };

        if ($max === null || $max === 0) {
            return 0; // 무제한인 경우
        }

        return min(($current / $max) * 100, 100);
    }
}
