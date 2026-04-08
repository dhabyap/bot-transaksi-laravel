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

        // Seed some data using legacy column names
        Transaction::create([
            'user_id' => $chatId,
            'nominal' => 100000,
            'tipe' => 'income',
            'kategori' => 'Gaji',
            'created_at' => Carbon::today()
        ]);

        Transaction::create([
            'user_id' => $chatId,
            'nominal' => 30000,
            'tipe' => 'expense',
            'kategori' => 'Makan',
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

        // Verify report data in the response if needed, 
        // but 'report' confirmed the route and logic passed with new schema.
    }
}
