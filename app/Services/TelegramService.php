<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected string $token;
    protected string $baseUrl;

    public function __construct()
    {
        $this->token = config('services.telegram.bot_token');
        $this->baseUrl = "https://api.telegram.org/bot{$this->token}";
    }

    /**
     * Send a message to a specific chat ID.
     */
    public function sendMessage(int|string $chatId, string $text): bool
    {
        try {
            $response = Http::post("{$this->baseUrl}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'Markdown',
            ]);

            if (!$response->successful()) {
                Log::error("Telegram API Error: " . $response->body());
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Telegram Service Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get basic information about the bot.
     */
    public function getMe(): array|bool
    {
        try {
            $response = Http::get("{$this->baseUrl}/getMe");
            return $response->successful() ? $response->json() : false;
        } catch (\Exception $e) {
            Log::error("Telegram getMe Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Set the webhook for the bot.
     */
    public function setWebhook(string $url): array|bool
    {
        try {
            $response = Http::post("{$this->baseUrl}/setWebhook", [
                'url' => $url
            ]);
            return $response->successful() ? $response->json() : false;
        } catch (\Exception $e) {
            Log::error("Telegram setWebhook Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get information about the current webhook status.
     */
    public function getWebhookInfo(): array|bool
    {
        try {
            $response = Http::get("{$this->baseUrl}/getWebhookInfo");
            return $response->successful() ? $response->json() : false;
        } catch (\Exception $e) {
            Log::error("Telegram getWebhookInfo Exception: " . $e->getMessage());
            return false;
        }
    }
}
