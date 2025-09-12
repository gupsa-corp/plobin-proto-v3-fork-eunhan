<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PointTransaction extends Model
{
    protected $fillable = [
        'organization_id',
        'transaction_type',
        'amount',
        'balance_before',
        'balance_after',
        'reason',
        'description',
        'reference_type',
        'reference_id',
        'processed_by',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    // 거래 타입 상수
    const TYPE_EARN = 'earn';
    const TYPE_SPEND = 'spend';
    const TYPE_REFUND = 'refund';
    const TYPE_ADMIN_ADJUST = 'admin_adjust';

    // 거래 사유 상수
    const REASON_PAYMENT = 'payment';
    const REASON_BONUS = 'bonus';
    const REASON_REFUND = 'refund';
    const REASON_ADMIN_ADJUSTMENT = 'admin_adjustment';
    const REASON_SUBSCRIPTION = 'subscription';
    const REASON_REFERRAL = 'referral';

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * 관련 모델 가져오기
     */
    public function reference()
    {
        if ($this->reference_type && $this->reference_id) {
            return $this->reference_type::find($this->reference_id);
        }
        return null;
    }

    /**
     * 포인트 금액 포맷팅
     */
    public function getFormattedAmount(): string
    {
        $prefix = $this->amount >= 0 ? '+' : '';
        return $prefix . number_format($this->amount) . 'P';
    }

    /**
     * 거래 타입 텍스트
     */
    public function getTransactionTypeText(): string
    {
        return match($this->transaction_type) {
            self::TYPE_EARN => '적립',
            self::TYPE_SPEND => '사용',
            self::TYPE_REFUND => '환불',
            self::TYPE_ADMIN_ADJUST => '관리자 조정',
            default => $this->transaction_type
        };
    }

    /**
     * 거래 사유 텍스트
     */
    public function getReasonText(): string
    {
        return match($this->reason) {
            self::REASON_PAYMENT => '결제',
            self::REASON_BONUS => '보너스',
            self::REASON_REFUND => '환불',
            self::REASON_ADMIN_ADJUSTMENT => '관리자 조정',
            self::REASON_SUBSCRIPTION => '구독',
            self::REASON_REFERRAL => '추천',
            default => $this->reason
        };
    }

    /**
     * 거래 타입별 색상
     */
    public function getTypeColor(): string
    {
        return match($this->transaction_type) {
            self::TYPE_EARN => 'green',
            self::TYPE_SPEND => 'red',
            self::TYPE_REFUND => 'blue',
            self::TYPE_ADMIN_ADJUST => 'yellow',
            default => 'gray'
        };
    }

    /**
     * 날짜 포맷팅
     */
    public function getFormattedDate(): string
    {
        return $this->created_at->format('Y.m.d H:i');
    }

    /**
     * 스코프: 특정 거래 타입
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('transaction_type', $type);
    }

    /**
     * 스코프: 특정 사유
     */
    public function scopeOfReason($query, string $reason)
    {
        return $query->where('reason', $reason);
    }

    /**
     * 스코프: 날짜 범위
     */
    public function scopeDateRange($query, Carbon $from, Carbon $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }
}