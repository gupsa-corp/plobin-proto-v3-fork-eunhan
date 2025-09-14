<?php

namespace App\Services\Point\GetPointStatistics;

use App\Models\Organization;

class Service
{
    public function __invoke(Organization $organization): array
    {
        $pointAccount = $organization->pointAccount;

        if (!$pointAccount) {
            return [
                'current_balance' => 0,
                'lifetime_earned' => 0,
                'lifetime_spent' => 0,
                'retention_rate' => 0,
                'transaction_count' => 0,
            ];
        }

        $transactionCount = $organization->pointTransactions()->count();

        return [
            'current_balance' => $pointAccount->current_balance,
            'lifetime_earned' => $pointAccount->lifetime_earned,
            'lifetime_spent' => $pointAccount->lifetime_spent,
            'retention_rate' => $pointAccount->getRetentionRate(),
            'transaction_count' => $transactionCount,
        ];
    }
}