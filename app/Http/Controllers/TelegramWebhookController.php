<?php

namespace App\Http\Controllers;

use App\Services\AI\AIManager;
use App\Services\TelegramService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    protected TelegramService $telegram;
    protected AIManager $ai;
    protected TransactionService $transactionService;

    public function __construct(TelegramService $telegram, AIManager $ai, TransactionService $transactionService)
    {
        $this->telegram = $telegram;
        $this->ai = $ai;
        $this->transactionService = $transactionService;
    }

    /**
     * Handle incoming Telegram webhook request.
     */
    public function handle(Request $request)
    {
        $payload = $request->all();

        // 1. Basic Validation
        if (!isset($payload['message'])) {
            return response()->json(['status' => 'ignored']);
        }

        $message = $payload['message'];
        $chatId = $message['chat']['id'] ?? null;
        $text = $message['text'] ?? '';

        if (!$chatId) {
            return response()->json(['status' => 'error', 'message' => 'No chat ID found']);
        }

        // 2. Security Guard: Authorization Check
        if (!$this->isAuthorized($chatId)) {
            Log::warning("Unauthorized access attempt from Chat ID: {$chatId}");
            $this->telegram->sendMessage($chatId, "🚫 Maaf, Anda tidak memiliki akses ke bot ini.");
            return response()->json(['status' => 'unauthorized']);
        }

        // 3. Command vs NLP Router
        if (str_starts_with($text, '/')) {
            return $this->handleCommand($chatId, $text);
        }

        return $this->handleNaturalLanguage($chatId, $text);
    }

    /**
     * Check if the user is authorized based on environment configuration.
     */
    protected function isAuthorized(int|string $chatId): bool
    {
        $authorizedIds = config('services.telegram.authorized_ids');
        
        if (empty($authorizedIds)) {
            return true; // If none configured, allow all (be careful!)
        }

        $idArray = explode(',', $authorizedIds);
        return in_array((string)$chatId, $idArray);
    }

    /**
     * Handle commands starting with /
     */
    protected function handleCommand($chatId, $text)
    {
        $command = explode(' ', $text)[0];

        switch ($command) {
            case '/start':
                $message = "👋 Halo! Saya adalah Bot Transaksi AI Anda.\n\nKirimkan pesan teks seperti:\n- \"Beli kopi 20rb\"\n- \"Gajian 5 juta\"\n\nSaya akan mencatatnya secara otomatis!";
                break;
            case '/help':
                $message = "ℹ️ **Bantuan**\n\nUntuk mencatat transaksi, cukup ketik detailnya. Contoh:\n\"Makan siang 25000\"\n\"Topup e-wallet 100rb\"";
                break;
            default:
                $message = "❓ Perintah tidak dikenal. Ketik /help untuk bantuan.";
                break;
        }

        $this->telegram->sendMessage($chatId, $message);
        return response()->json(['status' => 'success', 'type' => 'command']);
    }

    /**
     * Handle natural language text (NLP)
     */
    protected function handleNaturalLanguage($chatId, $text)
    {
        $parsed = $this->ai->parseTransaction($text);

        if (!$parsed) {
            $this->telegram->sendMessage($chatId, "🤔 Maaf, saya tidak mengerti maksud Anda. Bisa diulangi dengan lebih jelas? (Contoh: \"Beli bakso 15rb\")");
            return response()->json(['status' => 'success', 'type' => 'nlp_failed']);
        }

        // Save to database
        $transaction = $this->transactionService->createFromTelegram($parsed, (string)$chatId);

        if (!$transaction) {
            $this->telegram->sendMessage($chatId, "⚠️ Terjadi kesalahan saat menyimpan data. Silakan coba lagi nanti.");
            return response()->json(['status' => 'error', 'message' => 'Failed to save transaction']);
        }

        $type = ($parsed['type'] === 'income') ? '🟢 Pemasukan' : '🔴 Pengeluaran';
        $amount = number_format($parsed['amount'], 0, ',', '.');
        $category = $parsed['category'] ?? 'Umum';
        $description = $parsed['description'] ?? '-';
        $driver = $parsed['_ai_driver'] ?? 'unknown';

        $reply = "✅ **Berhasil Dicatat!**\n\n" .
                 "• **Tipe**: {$type}\n" .
                 "• **Jumlah**: Rp {$amount}\n" .
                 "• **Kategori**: {$category}\n" .
                 "• **Deskripsi**: {$description}\n\n" .
                 "*(Data telah aman disimpan di database)*";
        
        $this->telegram->sendMessage($chatId, $reply);
        return response()->json(['status' => 'success', 'type' => 'nlp_parsed', 'data' => $parsed]);
    }
}
