<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SmsVerification extends Model
{
    protected $fillable = [
        'phone_number',
        'country_code',
        'verification_code',
        'expires_at',
        'is_verified',
        'verified_at',
        'attempt_count',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'is_verified' => 'boolean',
    ];

    /**
     * 인증 코드가 만료되었는지 확인
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * 인증 코드가 유효한지 확인
     */
    public function isValid(): bool
    {
        return !$this->is_verified && !$this->isExpired();
    }

    /**
     * 인증 코드 검증
     */
    public function verify(string $code): bool
    {
        if (!$this->isValid() || $this->verification_code !== $code) {
            return false;
        }

        $this->update([
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        return true;
    }

    /**
     * 특정 전화번호의 최근 인증 시도 횟수 증가
     */
    public function incrementAttempts(): void
    {
        $this->increment('attempt_count');
    }

    /**
     * 특정 전화번호의 유효한 인증 코드 조회
     */
    public static function getValidVerification(string $phoneNumber, string $countryCode = '+82'): ?self
    {
        return static::where('phone_number', $phoneNumber)
            ->where('country_code', $countryCode)
            ->where('is_verified', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    }

    /**
     * 특정 전화번호의 일일 인증 시도 횟수 조회
     */
    public static function getDailyAttempts(string $phoneNumber, string $countryCode = '+82'): int
    {
        return static::where('phone_number', $phoneNumber)
            ->where('country_code', $countryCode)
            ->where('created_at', '>=', now()->startOfDay())
            ->count();
    }

    /**
     * 특정 IP의 시간당 인증 시도 횟수 조회
     */
    public static function getHourlyAttemptsByIp(string $ipAddress): int
    {
        return static::where('ip_address', $ipAddress)
            ->where('created_at', '>=', now()->subHour())
            ->count();
    }
}
