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

    public function process(string $text, array $context = []): ?array
    {
        $config = config('services.gemini');
        
        if (empty($config['key'])) {
            return null;
        }

        $categories = !empty($context['categories']) ? implode(', ', $context['categories']) : 'None yet';
        $today = $context['today'] ?? now()->toDateString();

        try {
            $response = Http::post($config['endpoint'] . "?key=" . $config['key'], [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => "Classify and extract data from human text.
                            Today's Date: {$today}. 
                            Existing categories: [{$categories}]. Use these if appropriate.

                            Return a JSON object with:
                            - 'intent': 'RECORD' or 'REPORT'.
                            - If 'RECORD', add 'data': { 'type': 'income'|'expense', 'amount': int, 'category': string, 'description': string }.
                            - If 'REPORT', add 'params': { 'range': 'today'|'week'|'month' }.
                            
                            Input: \"{$text}\"
                            ONLY return the JSON, no other text. Remove markdown formatting like ```json."
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
                $cleanContent = preg_replace('/```json\s*|\s*```/', '', $content);
                return json_decode(trim($cleanContent), true);
            }

            if ($response->status() === 429) {
                return ['error' => 'busy', 'code' => 429];
            }

            Log::error("Gemini API Error: " . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error("Gemini Driver Exception: " . $e->getMessage());
            return null;
        }
    }
}
