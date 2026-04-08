<?php

namespace App\Services;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Get a summary of transactions for a user within a specific range.
     * 
     * @param string $telegramUserId
     * @param string $range 'today', 'week', 'month'
     * @return array
     */
    public function getSummary(string $telegramUserId, string $range = 'today'): array
    {
        $query = Transaction::where('telegram_user_id', $telegramUserId);

        switch ($range) {
            case 'week':
                $query->where('created_at', '>=', Carbon::now()->startOfWeek());
                break;
            case 'month':
                $query->where('created_at', '>=', Carbon::now()->startOfMonth());
                break;
            default: // today
                $query->whereDate('created_at', Carbon::today());
                break;
        }

        $stats = $query->select(
            DB::raw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income"),
            DB::raw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense")
        )->first();

        $income = (float)($stats->total_income ?? 0);
        $expense = (float)($stats->total_expense ?? 0);

        return [
            'range' => $range,
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
            'count' => $query->count()
        ];
    }

    /**
     * Get expense breakdown by category.
     */
    public function getCategoryBreakdown(string $telegramUserId, string $range = 'month'): array
    {
        $query = Transaction::where('telegram_user_id', $telegramUserId)
            ->where('type', 'expense');

        if ($range === 'month') {
            $query->where('created_at', '>=', Carbon::now()->startOfMonth());
        }

        return $query->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->toArray();
    }
}
