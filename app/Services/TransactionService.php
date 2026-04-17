<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class TransactionService
{
    /**
     * Create a new transaction from parsed Telegram data.
     * 
     * @param array $data [type, amount, category, description, _ai_driver]
     * @param string $telegramUserId
     * @return Transaction|null
     */
    public function createFromTelegram(array $data, string $telegramUserId): ?Transaction
    {
        try {
            return Transaction::create([
                'user_id' => $telegramUserId,
                'tipe' => $data['type'],
                'nominal' => $data['amount'],
                'kategori' => $data['category'] ?? 'Umum',
                'item' => $data['description'] ?? null,
                'timestamp' => now(), // Fill the legacy timestamp column
                'metadata' => [
                    'ai_driver' => $data['_ai_driver'] ?? 'unknown',
                    'source' => 'telegram'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("Transaction Service Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get transaction history for a user.
     */
    public function getHistory(string $telegramUserId, int $limit = 10)
    {
        return Transaction::where('user_id', $telegramUserId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Update an existing transaction if it belongs to the user.
     */
    public function updateTransaction(int $id, string $telegramUserId, array $data): ?Transaction
    {
        try {
            $transaction = Transaction::where('id', $id)
                ->where('user_id', $telegramUserId)
                ->first();

            if (!$transaction) {
                return null;
            }

            $transaction->update([
                'tipe' => $data['type'] ?? $transaction->tipe,
                'nominal' => $data['amount'] ?? $transaction->nominal,
                'kategori' => $data['category'] ?? $transaction->kategori,
                'item' => $data['description'] ?? $transaction->item,
                'metadata' => array_merge($transaction->metadata ?? [], [
                    'updated_at' => now()->toDateTimeString(),
                    'ai_driver' => $data['_ai_driver'] ?? ($transaction->metadata['ai_driver'] ?? 'unknown')
                ])
            ]);

            return $transaction;
        } catch (\Exception $e) {
            Log::error("Update Transaction Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete a transaction if it belongs to the user.
     */
    public function deleteTransaction(int $id, string $telegramUserId): bool
    {
        try {
            $deleted = Transaction::where('id', $id)
                ->where('user_id', $telegramUserId)
                ->delete();

            return $deleted > 0;
        } catch (\Exception $e) {
            Log::error("Delete Transaction Error: " . $e->getMessage());
            return false;
        }
    }
}
