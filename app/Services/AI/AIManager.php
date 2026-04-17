<?php

namespace App\Services\AI;

use App\Models\Transaction;
use App\Services\AI\Drivers\GeminiDriver;
use App\Services\AI\Drivers\GroqDriver;
use Carbon\Carbon;
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
    public function processMessage(string $text, string $telegramUserId = null): ?array
    {
        $context = [
            'today' => Carbon::now()->isoFormat('dddd, D MMMM YYYY'),
            'categories' => []
        ];

        if ($telegramUserId) {
            $context['categories'] = Transaction::where('user_id', $telegramUserId)
                ->distinct()
                ->pluck('kategori')
                ->toArray();
        }

        $priorityString = config('services.ai.priority', 'groq,gemini');
        $priorityOrder = explode(',', $priorityString);

        $wasBusy = false;

        foreach ($priorityOrder as $driverName) {
            $driverName = trim($driverName);
            
            if (!isset($this->drivers[$driverName])) {
                continue;
            }

            $driver = $this->drivers[$driverName];
            Log::info("Attempting to process message with: {$driverName}");

            $result = $driver->process($text, $context);

            if ($result !== null) {
                // Check if driver explicitly reported being busy
                if (isset($result['error']) && $result['error'] === 'busy') {
                    Log::warning("AI Driver {$driverName} is busy (429). Trying next driver...");
                    $wasBusy = true;
                    continue;
                }

                if (isset($result['intent'])) {
                    // Strict Validation for RECORD
                    if ($result['intent'] === 'RECORD') {
                        if (!isset($result['data']['amount'], $result['data']['type'])) {
                            Log::warning("AI Driver {$driverName} returned RECORD without required fields. Trying next...");
                            continue;
                        }
                    }

                    // Attach driver info for audit/logging
                    $result['_ai_driver'] = $driverName;
                    return $result;
                }
            }

            Log::warning("AI Driver {$driverName} failed or returned null. Trying next...");
        }

        if ($wasBusy) {
            return ['error' => 'busy'];
        }

        Log::error("All AI Drivers failed to process message: {$text}");
        return null;
    }
}
