<?php

namespace App\Services\AI\Drivers;

use App\Services\AI\LLMDriverInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiDriver implements LLMDriverInterface
{
    public function getName(): string
    {
        return 'gemini';
    }

    public function parse(string $text): ?array
    {
        $config = config('services.gemini');
        
        if (empty($config['key'])) {
            return null;
        }

        try {
            $response = Http::post($config['endpoint'] . "?key=" . $config['key'], [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => "Extract transaction data from the text into a valid JSON object with keys: 
                            'type' (must be 'income' or 'expense'), 
                            'amount' (integer), 
                            'category' (short string), 
                            'description' (short string). 
                            If it's not a transaction, return exactly: null. 
                            Text: \"{$text}\"
                            ONLY return the JSON or null, no other text. 
                            Remove any markdown formatting like ```json."
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.1,
                ]
            ]);

            if ($response->successful()) {
                $content = $response->json('candidates.0.content.parts.0.text');
                
                // Gemini might return text with markdown blocks even if asked not to
                $cleanContent = preg_replace('/```json\s*|\s*```/', '', $content);
                
                return json_decode(trim($cleanContent), true);
            }

            Log::error("Gemini API Error: " . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error("Gemini Driver Exception: " . $e->getMessage());
            return null;
        }
    }
}
