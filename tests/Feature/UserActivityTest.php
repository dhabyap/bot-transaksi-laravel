<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\BotUser;
use App\Models\ChatLog;
use Illuminate\Support\Facades\Http;

class UserActivityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that user activity is correctly tracked and logged.
     */
    public function test_user_activity_is_tracked_on_webhook(): void
    {
        $chatId = 777777;
        $username = 'testuser';

        config(['services.telegram.authorized_ids' => (string)$chatId]);

        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true], 200),
        ]);

        // Send first message
        $response = $this->postJson('/api/webhook/telegram', [
            'message' => [
                'chat' => ['id' => $chatId],
                'from' => [
                    'id' => $chatId,
                    'first_name' => 'Test',
                    'username' => $username,
                    'language_code' => 'id'
                ],
                'text' => 'Halo bot'
            ]
        ]);

        $response->assertStatus(200);

        // Verify BotUser created
        $this->assertDatabaseHas('bot_users', [
            'user_id' => $chatId,
            'username' => $username,
            'message_count' => 1
        ]);

        // Verify ChatLog created
        $this->assertDatabaseHas('chat_logs', [
            'user_id' => $chatId,
            'message' => 'Halo bot'
        ]);

        // Send second message
        $this->postJson('/api/webhook/telegram', [
            'message' => [
                'chat' => ['id' => $chatId],
                'from' => [
                    'id' => $chatId,
                    'first_name' => 'Test'
                ],
                'text' => 'Pesan kedua'
            ]
        ]);

        // Verify message_count incremented
        $user = BotUser::find($chatId);
        $this->assertEquals(2, $user->message_count);
        
        // Verify multiple logs
        $this->assertEquals(2, ChatLog::where('user_id', $chatId)->count());
    }
}
