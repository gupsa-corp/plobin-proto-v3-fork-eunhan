<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Propaganistas\LaravelPhone\PhoneNumber;
use App\Services\PhoneNumberHelper;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Organization;
use App\Models\OrganizationMember;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles, LogsActivity;

    protected $fillable = [
        'email',
        'email_verified_at',
        'password',
        'country_code',
        'phone_number',
        'phone_verified_at',
        'nickname',
        'first_name',
        'last_name',
        'name',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getNameAttribute(): string
    {
        return $this->attributes['name'] ?? $this->display_name;
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->nickname) {
            return $this->nickname;
        }

        $fullName = $this->full_name;
        if ($fullName) {
            return $fullName;
        }

        return explode('@', $this->email)[0];
    }

    public function getPhoneNumberInstance(): ?PhoneNumber
    {
        return PhoneNumberHelper::createPhoneNumber($this->phone_number ?? '', $this->country_code ?? '');
    }


    public function getFormattedPhoneAttribute(): ?string
    {
        return PhoneNumberHelper::formatNational($this->phone_number ?? '', $this->country_code ?? '') ?? $this->phone_number;
    }

    public function getE164PhoneAttribute(): ?string
    {
        return PhoneNumberHelper::formatE164($this->phone_number ?? '', $this->country_code ?? '');
    }

    public function getInternationalPhoneAttribute(): ?string
    {
        return PhoneNumberHelper::formatInternational($this->phone_number ?? '', $this->country_code ?? '');
    }

    public function isValidPhone(): bool
    {
        return PhoneNumberHelper::isValid($this->phone_number ?? '', $this->country_code ?? '');
    }

    public function getPhoneTypeAttribute(): ?string
    {
        return PhoneNumberHelper::getPhoneType($this->phone_number ?? '', $this->country_code ?? '');
    }

    /**
     * 휴대폰 인증 여부 확인
     */
    public function hasVerifiedPhone(): bool
    {
        return !is_null($this->phone_verified_at);
    }

    /**
     * 휴대폰 인증 완료 처리
     */
    public function markPhoneAsVerified(): void
    {
        $this->phone_verified_at = now();
        $this->save();
    }

    /**
     * 휴대폰 인증 해제 처리
     */
    public function markPhoneAsUnverified(): void
    {
        $this->phone_verified_at = null;
        $this->save();
    }

    public function organizationMemberships(): HasMany
    {
        return $this->hasMany(OrganizationMember::class);
    }

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'organization_members')
                    ->withPivot(['role_name', 'joined_at', 'invited_at', 'invitation_status'])
                    ->withTimestamps();
    }

    public function organization()
    {
        return $this->organizations()->limit(1);
    }

    /**
     * 동적 권한 체크 - 편리한 메소드
     */
    public function canPerform(string $resourceType, string $action, array $context = []): bool
    {
        return app(\App\Services\DynamicPermissionService::class)
            ->canPerformAction($this, $resourceType, $action, $context);
    }

    /**
     * 조직 내 특정 역할 이상인지 확인
     */
    public function hasOrganizationRole(\App\Enums\ProjectRole $role, $organizationId = null): bool
    {
        $organizationId = $organizationId ?? request()->route('organization');
        
        $organizationMember = $this->organizationMemberships()
            ->where('organization_id', $organizationId)
            ->first();

        if (!$organizationMember) {
            return false;
        }

        return $organizationMember->hasRoleOrHigher($role);
    }

    /**
     * 사용자의 권한 요약 반환
     */
    public function getPermissionSummary(): array
    {
        return app(\App\Services\DynamicPermissionService::class)
            ->getUserPermissionSummary($this);
    }

    /**
     * 플랫폼 관리자 페이지용 사용자 목록 조회
     */
    public static function getUsersWithRoles()
    {
        return static::with(['organizations'])
            ->withCount('organizations')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($user) => $user->toArrayWithRole());
    }

    /**
     * 사용자 정보를 배열로 변환
     */
    public function toArrayWithRole(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'organizations_count' => $this->organizations_count,
            'primary_organization' => $this->organizations->first()?->name ?? '소속없음',
            'status' => 'active', // 기본값, 실제 구현시 사용자 상태 필드 추가
            'last_login' => $this->updated_at->format('Y-m-d H:i'),
            'created_at' => $this->created_at->format('Y-m-d H:i')
        ];
    }

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'nickname'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
