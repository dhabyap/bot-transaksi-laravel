<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;

class AIContextTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that AIManager correctly provides context to drivers.
     */
    public function test_ai_receives_user_context(): void
    {
        $chatId = '888888';

        // Seed an existing category
        Transaction::create([
            'telegram_user_id' => $chatId,
            'amount' => 1000,
            'type' => 'expense',
            'category' => 'Hobi',
        ]);

        config(['services.groq.key' => 'test-key']);

        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true], 200),
            'api.groq.com/*' => function ($request) {
                $body = json_decode($request->body(), true);
                $systemPrompt = $body['messages'][0]['content'];

                // Verify context injection in system prompt
                if (str_contains($systemPrompt, 'Hobi')) {
                    return Http::response([
                        'choices' => [
                            [
                                'message' => [
                                    'content' => json_encode([
                                        'intent' => 'RECORD',
                                        'data' => [
                                            'type' => 'expense',
                                            'amount' => 5000,
                                            'category' => 'Hobi' // Should prefer existing category
                                        ]
                                    ])
                                ]
                            ]
                        ]
                    ], 200);
                }

                return Http::response(null, 500);
            },
        ]);

        $response = $this->postJson('/api/webhook/telegram', [
            'message' => [
                'chat' => ['id' => $chatId],
                'text' => 'Beli kartu pokemon 5rb'
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success', 'type' => 'record']);
        
        $this->assertDatabaseHas('transactions', [
            'telegram_user_id' => $chatId,
            'category' => 'Hobi'
        ]);
    }
}
