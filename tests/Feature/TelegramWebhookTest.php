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
        // Mocking external Telegram API call in TelegramService
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
     * Test logic for natural language text.
     */
    public function test_telegram_webhook_handles_nlp_text(): void
    {
        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true], 200),
        ]);

        $response = $this->postJson('/api/webhook/telegram', [
            'message' => [
                'chat' => ['id' => 123456],
                'text' => 'Beli kopi 20000'
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success', 'type' => 'nlp']);
    }
}
