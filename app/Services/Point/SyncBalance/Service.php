<?php

namespace App\Services\Point\SyncBalance;

use App\Models\Organization;
use Illuminate\Support\Facades\DB;

class Service
{
    public function __invoke(Organization $organization): bool
    {
        return DB::transaction(function () use ($organization) {
            $pointAccount = $organization->pointAccount;

            if (!$pointAccount) {
                return false;
            }

            // 거래 내역을 기반으로 실제 잔액 계산
            $actualBalance = $organization->pointTransactions()
                ->sum('amount');

            // 불일치가 있으면 수정
            if ($pointAccount->current_balance != $actualBalance) {
                $pointAccount->update(['current_balance' => $actualBalance]);
                $organization->update(['points_balance' => $actualBalance]);

                return true; // 동기화 수행됨
            }

            return false; // 동기화 불필요
        });
    }
}