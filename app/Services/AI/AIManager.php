<?php

namespace App\Services\AI;

use App\Services\AI\Drivers\GeminiDriver;
use App\Services\AI\Drivers\GroqDriver;
use Illuminate\Support\Facades\Log;

class AIManager
{
    protected array $drivers = [];

    public function __construct(GroqDriver $groq, GeminiDriver $gemini)
    {
        // Register available drivers
        $this->drivers = [
            'groq' => $groq,
            'gemini' => $gemini,
        ];
    }

    /**
     * Parse text by trying drivers in the priority order.
     */
    public function parseTransaction(string $text): ?array
    {
        $priorityString = config('services.ai.priority', 'groq,gemini');
        $priorityOrder = explode(',', $priorityString);

        foreach ($priorityOrder as $driverName) {
            $driverName = trim($driverName);
            
            if (!isset($this->drivers[$driverName])) {
                continue;
            }

            $driver = $this->drivers[$driverName];
            Log::info("Attempting to parse transaction with: {$driverName}");

            $result = $driver->parse($text);

            if ($result !== null) {
                // Attach driver info for audit/logging
                $result['_ai_driver'] = $driverName;
                return $result;
            }

            Log::warning("AI Driver {$driverName} failed or returned null. Trying next...");
        }

        Log::error("All AI Drivers failed to parse text: {$text}");
        return null;
    }
}
