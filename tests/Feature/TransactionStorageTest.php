<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use App\Models\Transaction;

class TransactionStorageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a transaction is correctly saved after being parsed by AI.
     */
    public function test_transaction_is_saved_to_database(): void
    {
        // Mocking AI Driver
        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true], 200),
            'api.groq.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'intent' => 'RECORD',
                                'data' => [
                                    'type' => 'expense',
                                    'amount' => 50000,
                                    'category' => 'Bensin',
                                    'description' => 'Beli bensin pertamax'
                                ]
                            ])
                        ]
                    ]
                ]
            ], 200),
        ]);

        config(['services.groq.key' => 'test-key']);

        $chatId = '123456789';
        $response = $this->postJson('/api/webhook/telegram', [
            'message' => [
                'chat' => ['id' => $chatId],
                'text' => 'Beli bensin 50rb pertamax'
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success', 'type' => 'record']);

        // Verify Database
        $this->assertDatabaseHas('transactions', [
            'telegram_user_id' => $chatId,
            'amount' => 50000,
            'category' => 'Bensin',
            'type' => 'expense'
        ]);

        $transaction = Transaction::first();
        $this->assertEquals('groq', $transaction->metadata['ai_driver']);
    }
}
