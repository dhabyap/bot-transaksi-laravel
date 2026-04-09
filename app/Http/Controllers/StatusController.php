<?php

namespace App\Http\Controllers;

use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StatusController extends Controller
{
    protected TelegramService $telegram;

    public function __construct(TelegramService $telegram)
    {
        $this->telegram = $telegram;
    }

    /**
     * Get the health status of the application and its dependencies.
     */
    public function index()
    {
        $status = [
            'app' => [
                'status' => 'UP',
                'environment' => config('app.env'),
                'debug' => config('app.debug'),
            ],
            'database' => $this->checkDatabase(),
            'telegram' => $this->checkTelegram(),
            'ai_services' => $this->checkAIServices(),
        ];

        return response()->json($status);
    }

    /**
     * Check database connectivity.
     */
    protected function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            return [
                'status' => 'CONNECTED',
                'driver' => config('database.default'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'DISCONNECTED',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check Telegram Bot API status.
     */
    protected function checkTelegram(): array
    {
        $info = $this->telegram->getMe();
        
        if ($info && isset($info['ok']) && $info['ok']) {
            return [
                'status' => 'CONNECTED',
                'bot_name' => $info['result']['first_name'],
                'username' => '@' . $info['result']['username'],
            ];
        }

        return [
            'status' => 'ERROR',
            'message' => 'Failed to connect to Telegram API. Check BOT_TOKEN.',
            'debug' => $info
        ];
    }

    /**
     * Check AI Service configuration.
     */
    protected function checkAIServices(): array
    {
        return [
            'groq' => [
                'configured' => !empty(config('services.groq.key')),
                'model' => config('services.groq.model'),
            ],
            'gemini' => [
                'configured' => !empty(config('services.gemini.key')),
            ],
            'priority' => config('services.ai.priority'),
        ];
    }
}
