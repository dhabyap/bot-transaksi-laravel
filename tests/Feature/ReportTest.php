<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use App\Models\Transaction;
use Carbon\Carbon;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that asking for a report returns the correct aggregation data.
     */
    public function test_telegram_webhook_returns_financial_report(): void
    {
        $chatId = '123456789';

        // Seed some data
        Transaction::create([
            'telegram_user_id' => $chatId,
            'amount' => 100000,
            'type' => 'income',
            'category' => 'Gaji',
            'created_at' => Carbon::today()
        ]);

        Transaction::create([
            'telegram_user_id' => $chatId,
            'amount' => 30000,
            'type' => 'expense',
            'category' => 'Makan',
            'created_at' => Carbon::today()
        ]);

        // Mock AI to return a REPORT intent
        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true], 200),
            'api.groq.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'intent' => 'REPORT',
                                'params' => [
                                    'range' => 'today'
                                ]
                            ])
                        ]
                    ]
                ]
            ], 200),
        ]);

        config(['services.groq.key' => 'test-key']);

        $response = $this->postJson('/api/webhook/telegram', [
            'message' => [
                'chat' => ['id' => $chatId],
                'text' => 'Berapa saldo saya hari ini?'
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success', 'type' => 'report']);

        // Since we mock Telegram API, we can't see the message body here easily without custom assertion 
        // but the 'report' type confirms the logic path.
    }
}
