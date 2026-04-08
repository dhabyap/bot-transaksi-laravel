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
}
