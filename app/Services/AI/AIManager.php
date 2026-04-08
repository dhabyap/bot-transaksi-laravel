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
     * Process text by trying drivers in the priority order to get intent and data.
     */
    public function processMessage(string $text): ?array
    {
        $priorityString = config('services.ai.priority', 'groq,gemini');
        $priorityOrder = explode(',', $priorityString);

        foreach ($priorityOrder as $driverName) {
            $driverName = trim($driverName);
            
            if (!isset($this->drivers[$driverName])) {
                continue;
            }

            $driver = $this->drivers[$driverName];
            Log::info("Attempting to process message with: {$driverName}");

            $result = $driver->process($text);

            if ($result !== null && isset($result['intent'])) {
                // Attach driver info for audit/logging
                $result['_ai_driver'] = $driverName;
                return $result;
            }

            Log::warning("AI Driver {$driverName} failed or returned null. Trying next...");
        }

        Log::error("All AI Drivers failed to process message: {$text}");
        return null;
    }
}
