<?php

namespace App\Services\AI\Drivers;

use App\Services\AI\LLMDriverInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqDriver implements LLMDriverInterface
{
    public function getName(): string
    {
        return 'groq';
    }

    public function process(string $text): ?array
    {
        $config = config('services.groq');
        
        if (empty($config['key'])) {
            return null;
        }

        try {
            $response = Http::withToken($config['key'])
                ->post($config['endpoint'], [
                    'model' => $config['model'],
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => "Classify and extract data from human text.
                            Return a JSON object with:
                            - 'intent': 'RECORD' (if user wants to save a transaction) or 'REPORT' (if user asks for summary/insight).
                            - If 'RECORD', add 'data': { 'type': 'income'|'expense', 'amount': int, 'category': string, 'description': string }.
                            - If 'REPORT', add 'params': { 'range': 'today'|'week'|'month' }.
                            
                            Example RECORD: \"Beli bakso 15rb\" -> { \"intent\": \"RECORD\", \"data\": { ... } }
                            Example REPORT: \"Pengeluaran saya hari ini\" -> { \"intent\": \"REPORT\", \"params\": { \"range\": \"today\" } }
                            
                            If unknown, return: null. ONLY JSON or null."
                        ],
                        ['role' => 'user', 'content' => $text]
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'temperature' => 0.1,
                ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');
                return json_decode($content, true);
            }

            Log::error("Groq API Error: " . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error("Groq Driver Exception: " . $e->getMessage());
            return null;
        }
    }
}
