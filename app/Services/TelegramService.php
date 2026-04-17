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

    /**
     * Delete the webhook.
     */
    public function deleteWebhook(): array|bool
    {
        try {
            $response = Http::post("{$this->baseUrl}/deleteWebhook");
            return $response->successful() ? $response->json() : false;
        } catch (\Exception $e) {
            Log::error("Telegram deleteWebhook Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get updates (for long polling).
     */
    public function getUpdates(int $offset = 0, int $timeout = 30): array|bool
    {
        try {
            // HTTP client timeout must be LONGER than the Telegram long-polling timeout
            // otherwise cURL will kill the connection before Telegram responds
            $response = Http::timeout($timeout + 5)
                ->get("{$this->baseUrl}/getUpdates", [
                    'offset' => $offset,
                    'timeout' => $timeout,
                    'allowed_updates' => ['message']
                ]);
            return $response->successful() ? $response->json() : false;
        } catch (\Exception $e) {
            Log::error("Telegram getUpdates Exception: " . $e->getMessage());
            return false;
        }
    }
}
