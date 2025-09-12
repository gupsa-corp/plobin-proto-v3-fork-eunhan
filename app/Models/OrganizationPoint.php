<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationPoint extends Model
{
    protected $fillable = [
        'organization_id',
        'current_balance',
        'lifetime_earned',
        'lifetime_spent',
    ];

    protected $casts = [
        'current_balance' => 'decimal:2',
        'lifetime_earned' => 'decimal:2',
        'lifetime_spent' => 'decimal:2',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * 포인트 잔액 포맷팅
     */
    public function getFormattedBalance(): string
    {
        return number_format($this->current_balance) . 'P';
    }

    /**
     * 총 획득 포인트 포맷팅
     */
    public function getFormattedLifetimeEarned(): string
    {
        return number_format($this->lifetime_earned) . 'P';
    }

    /**
     * 총 사용 포인트 포맷팅
     */
    public function getFormattedLifetimeSpent(): string
    {
        return number_format($this->lifetime_spent) . 'P';
    }

    /**
     * 포인트 사용 가능 여부
     */
    public function canSpend(float $amount): bool
    {
        return $this->current_balance >= $amount;
    }

    /**
     * 포인트 적립률 계산 (총 획득 대비 현재 잔액)
     */
    public function getRetentionRate(): float
    {
        if ($this->lifetime_earned <= 0) {
            return 0;
        }

        return ($this->current_balance / $this->lifetime_earned) * 100;
    }
}