<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Http;

class TelegramWebhookTest extends TestCase
{
    /**
     * Test basic webhook routing for commands.
     */
    public function test_telegram_webhook_handles_start_command(): void
    {
        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true], 200),
        ]);

        $response = $this->postJson('/api/webhook/telegram', [
            'message' => [
                'chat' => ['id' => 123456],
                'text' => '/start'
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success', 'type' => 'command']);
    }

    /**
     * Test logic for natural language text with AI parsing RECORD intent.
     */
    public function test_telegram_webhook_handles_nlp_text_with_ai(): void
    {
        config(['services.groq.key' => 'dummy_groq_key']);

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
                                    'amount' => 20000,
                                    'category' => 'Food',
                                    'description' => 'Beli kopi'
                                ]
                            ])
                        ]
                    ]
                ]
            ], 200),
        ]);

        $response = $this->postJson('/api/webhook/telegram', [
            'message' => [
                'chat' => ['id' => 123456],
                'text' => 'Beli kopi 20000'
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success', 'type' => 'record']);
    }
}
