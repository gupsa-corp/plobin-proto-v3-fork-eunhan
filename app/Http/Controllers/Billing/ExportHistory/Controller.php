<?php

namespace App\Http\Controllers\Billing\ExportHistory;

use Illuminate\Http\Request;
use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public function export(Request $request, Organization $organization)
    {
        $query = $organization->billingHistories()->with(['subscription']);

        if ($request->filled('period')) {
            $period = $request->period;
            $startDate = match($period) {
                '6months' => Carbon::now()->subMonths(6),
                '1year' => Carbon::now()->subYear(),
                'all' => null,
                default => Carbon::now()->subMonths(6)
            };

            if ($startDate) {
                $query->where('approved_at', '>=', $startDate);
            }
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $status = $request->status;
            $statusMap = [
                'completed' => 'DONE',
                'failed' => ['CANCELED', 'PARTIAL_CANCELED', 'ABORTED', 'EXPIRED'],
                'refunded' => 'PARTIAL_CANCELED',
                'pending' => ['READY', 'IN_PROGRESS', 'WAITING_FOR_DEPOSIT']
            ];

            if (isset($statusMap[$status])) {
                if (is_array($statusMap[$status])) {
                    $query->whereIn('status', $statusMap[$status]);
                } else {
                    $query->where('status', $statusMap[$status]);
                }
            }
        }

        $billingHistories = $query->orderBy('approved_at', 'desc')->get();

        $filename = 'billing_history_' . $organization->name . '_' . Carbon::now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function() use ($billingHistories) {
            $file = fopen('php://output', 'w');

            fwrite($file, "\xEF\xBB\xBF");

            fputcsv($file, [
                '날짜',
                '설명',
                '결제수단',
                '금액',
                '상태',
                '주문번호',
                '결제키'
            ]);

            foreach ($billingHistories as $history) {
                fputcsv($file, [
                    $history->getFormattedDate(),
                    $history->description,
                    $history->card_company . ' **** ' . substr($history->card_number, -4),
                    $history->getFormattedAmount(),
                    $history->getStatusText(),
                    $history->order_id,
                    $history->payment_key
                ]);
            }

            fclose($file);
        }, 200, $headers);
    }
}
