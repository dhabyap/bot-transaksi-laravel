<?php

namespace App\Http\Controllers;

use App\Models\BotUser;
use App\Models\ChatLog;
use App\Services\AI\AIManager;
use App\Services\TelegramService;
use App\Services\TransactionService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TelegramWebhookController extends Controller
{
    protected TelegramService $telegram;
    protected AIManager $ai;
    protected TransactionService $transactionService;
    protected ReportService $reportService;

    public function __construct(
        TelegramService $telegram, 
        AIManager $ai, 
        TransactionService $transactionService,
        ReportService $reportService
    ) {
        $this->telegram = $telegram;
        $this->ai = $ai;
        $this->transactionService = $transactionService;
        $this->reportService = $reportService;
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

        // Log Activity & Tracking
        $this->logActivity($message);

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
                $message = "👋 Halo! Saya adalah Bot Transaksi AI Anda.\n\nKirimkan pesan teks seperti:\n- \"Beli kopi 20rb\"\n- \"Gajian 5 juta\"\n\nAtau tanya laporan:\n- \"Berapa pengeluaran saya hari ini?\"\n- \"Rekap minggu ini\"";
                break;
            case '/help':
                $message = "ℹ️ **Bantuan**\n\n- **Catat**: \"Makan siang 25rb\"\n- **Laporan**: \"Rekap hari ini\" atau gunakan perintah /rekap";
                break;
            case '/rekap':
                return $this->sendReport($chatId, 'today');
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
        $result = $this->ai->processMessage($text, (string)$chatId);

        if (!$result) {
            $this->telegram->sendMessage($chatId, "🤔 Maaf, saya tidak mengerti maksud Anda. Bisa diulangi dengan lebih jelas?");
            return response()->json(['status' => 'success', 'type' => 'nlp_failed']);
        }

        if ($result['intent'] === 'REPORT') {
            $range = $result['params']['range'] ?? 'today';
            return $this->sendReport($chatId, $range, $result['_ai_driver']);
        }

        if ($result['intent'] === 'RECORD' && isset($result['data'])) {
            return $this->recordTransaction($chatId, $result['data'], $result['_ai_driver']);
        }

        $this->telegram->sendMessage($chatId, "😅 Saya paham kata Anda, tapi belum yakin apa yang harus dilakukan.");
        return response()->json(['status' => 'success', 'type' => 'nlp_fallback']);
    }

    /**
     * Internal helper to record a transaction.
     */
    protected function recordTransaction($chatId, $data, $driver)
    {
        $data['_ai_driver'] = $driver;
        $transaction = $this->transactionService->createFromTelegram($data, (string)$chatId);

        if (!$transaction) {
            $this->telegram->sendMessage($chatId, "⚠️ Terjadi kesalahan saat menyimpan data.");
            return response()->json(['status' => 'error']);
        }

        $type = ($data['type'] === 'income') ? '🟢 Pemasukan' : '🔴 Pengeluaran';
        $amount = number_format($data['amount'], 0, ',', '.');
        $reply = "✅ **Berhasil Dicatat!** ({$driver})\n\n" .
                 "• **Tipe**: {$type}\n" .
                 "• **Jumlah**: Rp {$amount}\n" .
                 "• **Kategori**: " . ($data['category'] ?? 'Umum');
        
        $this->telegram->sendMessage($chatId, $reply);
        return response()->json(['status' => 'success', 'type' => 'record']);
    }

    /**
     * Internal helper to send a financial report.
     */
    protected function sendReport($chatId, $range, $driver = 'manual')
    {
        $summary = $this->reportService->getSummary((string)$chatId, $range);

        $rangeText = [
            'today' => 'Hari Ini',
            'week' => 'Minggu Ini',
            'month' => 'Bulan Ini'
        ][$range] ?? $range;

        $income = number_format($summary['income'], 0, ',', '.');
        $expense = number_format($summary['expense'], 0, ',', '.');
        $balance = number_format($summary['balance'], 0, ',', '.');

        $reply = "📊 **Laporan Keuangan ({$rangeText})**\n" .
                 "--------------------------\n" .
                 "🟢 Pemasukan: Rp {$income}\n" .
                 "🔴 Pengeluaran: Rp {$expense}\n" .
                 "--------------------------\n" .
                 "💰 **Saldo Bersih: Rp {$balance}**\n\n" .
                 "Jumlah Transaksi: {$summary['count']}";

        if ($range === 'month' || $range === 'today') {
            $breakdown = $this->reportService->getCategoryBreakdown((string)$chatId, $range);
            if (!empty($breakdown)) {
                $reply .= "\n\n📂 **Top Pengeluaran:**";
                foreach (array_slice($breakdown, 0, 3) as $item) {
                    $amt = number_format($item['total'], 0, ',', '.');
                    $reply .= "\n- {$item['category']}: Rp {$amt}";
                }
            }
        }

        $this->telegram->sendMessage($chatId, $reply);
        return response()->json(['status' => 'success', 'type' => 'report']);
    }

    /**
     * Handle GET requests to the webhook URL (fallback for browser visits).
     */
    public function verify()
    {
        return response()->json([
            'status' => 'active',
            'message' => 'Telegram Webhook is Active. This endpoint expects POST requests from Telegram API.',
            'documentation' => 'https://core.telegram.org/bots/api#setwebhook'
        ]);
    }

    /**
     * Log user activity and track message count.
     */
    protected function logActivity(array $message)
    {
        $from = $message['from'] ?? null;
        if (!$from) return;

        $userId = $from['id'];
        $now = now();

        // 1. Log Activity (User Tracking)
        $user = BotUser::find($userId);
        if (!$user) {
            BotUser::create([
                'user_id' => $userId,
                'first_name' => $from['first_name'] ?? null,
                'last_name' => $from['last_name'] ?? null,
                'username' => $from['username'] ?? null,
                'language_code' => $from['language_code'] ?? null,
                'first_seen' => $now,
                'last_active' => $now,
                'message_count' => 1
            ]);
        } else {
            $user->update([
                'first_name' => $from['first_name'] ?? $user->first_name,
                'last_name' => $from['last_name'] ?? $user->last_name,
                'username' => $from['username'] ?? $user->username,
                'last_active' => $now,
                'message_count' => $user->message_count + 1
            ]);
        }

        // 2. Log Chat Message
        ChatLog::create([
            'user_id' => $userId,
            'message' => $message['text'] ?? '',
            'timestamp' => $now
        ]);
    }
}
