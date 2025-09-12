<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Enums\ProjectRole;

class Organization extends Model
{
    protected $fillable = [
        'name',
        'url',
        'description',
        'user_id',
        'status',
        'members_count',
        'points_balance'
    ];

    protected $hidden = [
        'user_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(OrganizationMember::class);
    }

    public function organizationMembers(): HasMany
    {
        return $this->hasMany(OrganizationMember::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_members')
                    ->withPivot(['role_name', 'joined_at', 'invited_at', 'invitation_status'])
                    ->withTimestamps();
    }

    public function getMemberRole(User $user): ?ProjectRole
    {
        $member = $this->organizationMembers()->where('user_id', $user->id)->first();
        return $member ? $member->getRoleEnum() : null;
    }

    public function hasMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }

    public function addMember(User $user, ProjectRole $role = ProjectRole::GUEST): OrganizationMember
    {
        return $this->organizationMembers()->create([
            'user_id' => $user->id,
            'role_name' => $role->value,
            'invited_at' => now(),
            'joined_at' => now(),
            'invitation_status' => 'accepted',
        ]);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->where('status', 'active');
    }

    public function billingHistories(): HasMany
    {
        return $this->hasMany(BillingHistory::class);
    }

    public function businessInfo(): HasOne
    {
        return $this->hasOne(BusinessInfo::class);
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function activePaymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class)->active()->byPriority();
    }

    public function defaultPaymentMethod(): HasOne
    {
        return $this->hasOne(PaymentMethod::class)->where('is_default', true)->where('is_active', true);
    }

    public function primaryPaymentMethod(): HasOne
    {
        return $this->hasOne(PaymentMethod::class)->active()->orderBy('priority');
    }

    public function organizationLicenses(): HasMany
    {
        return $this->hasMany(OrganizationLicense::class);
    }

    public function activeLicenses(): HasMany
    {
        return $this->hasMany(OrganizationLicense::class)->active();
    }

    public function licensesByType(string $type): HasMany
    {
        return $this->hasMany(OrganizationLicense::class)->byType($type);
    }

    public function expiringSoonLicenses(int $days = 30): HasMany
    {
        return $this->hasMany(OrganizationLicense::class)->expiringSoon($days);
    }

    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription()->exists();
    }

    public function getCurrentUsage(): array
    {
        return [
            'members' => $this->members_count ?? $this->members()->count(),
            'projects' => $this->projects()->count(),
            'storage' => 0, // TODO: 실제 스토리지 사용량 계산
        ];
    }

    public function getTotalLicenseQuantity(string $type, string $unitType = 'seat'): int
    {
        return $this->activeLicenses()
            ->where('license_type', $type)
            ->where('unit_type', $unitType)
            ->sum('quantity');
    }

    public function getMonthlyLicenseCost(): float
    {
        return $this->activeLicenses()
            ->where('billing_cycle', 'monthly')
            ->sum('total_price');
    }

    public function getYearlyLicenseCost(): float
    {
        return $this->activeLicenses()
            ->where('billing_cycle', 'yearly')
            ->sum('total_price');
    }

    public function hasActiveLicense(string $type): bool
    {
        return $this->activeLicenses()->where('license_type', $type)->exists();
    }

    public function getLicensesExpiringSoon(int $days = 30): array
    {
        return $this->expiringSoonLicenses($days)
            ->get()
            ->map(function ($license) {
                return [
                    'id' => $license->id,
                    'name' => $license->license_name,
                    'type' => $license->license_type,
                    'expires_at' => $license->expires_at,
                    'days_until_expiry' => $license->getDaysUntilExpiry(),
                ];
            })
            ->toArray();
    }

    public function canAddMembers(int $count = 1): bool
    {
        $currentMembers = $this->getCurrentUsage()['members'];
        $totalSeats = $this->getTotalLicenseQuantity('pro', 'seat') + $this->getTotalLicenseQuantity('enterprise', 'seat');

        return ($currentMembers + $count) <= $totalSeats;
    }

    public function getRemainingSeats(): int
    {
        $currentMembers = $this->getCurrentUsage()['members'];
        $totalSeats = $this->getTotalLicenseQuantity('pro', 'seat') + $this->getTotalLicenseQuantity('enterprise', 'seat');

        return max(0, $totalSeats - $currentMembers);
    }

    // ========== 포인트 시스템 관련 메서드 ==========

    public function pointAccount(): HasOne
    {
        return $this->hasOne(OrganizationPoint::class);
    }

    public function pointTransactions(): HasMany
    {
        return $this->hasMany(PointTransaction::class);
    }

    /**
     * 포인트 잔액 조회 (성능 최적화용)
     */
    public function getPointsBalance(): float
    {
        return $this->points_balance ?? $this->pointAccount?->current_balance ?? 0;
    }

    /**
     * 포맷된 포인트 잔액
     */
    public function getFormattedPointsBalance(): string
    {
        return number_format($this->getPointsBalance()) . 'P';
    }

    /**
     * 포인트 사용 가능 여부
     */
    public function canSpendPoints(float $amount): bool
    {
        return $this->getPointsBalance() >= $amount;
    }

    /**
     * 포인트 계정 생성 또는 조회
     */
    public function getOrCreatePointAccount(): OrganizationPoint
    {
        return $this->pointAccount()->firstOrCreate([
            'organization_id' => $this->id
        ], [
            'current_balance' => 0,
            'lifetime_earned' => 0,
            'lifetime_spent' => 0,
        ]);
    }

    /**
     * 최근 포인트 거래 내역
     */
    public function getRecentPointTransactions(int $limit = 10)
    {
        return $this->pointTransactions()
            ->latest()
            ->limit($limit)
            ->get();
    }
}
