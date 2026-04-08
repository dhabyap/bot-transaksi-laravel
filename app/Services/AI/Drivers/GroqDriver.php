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

    public function parse(string $text): ?array
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
                            'content' => "Extract transaction data from the text into a valid JSON object with keys: 
                            'type' (must be 'income' or 'expense'), 
                            'amount' (integer), 
                            'category' (short string), 
                            'description' (short string). 
                            If it's not a transaction, return exactly: null. 
                            ONLY return the JSON or null, no other text."
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
