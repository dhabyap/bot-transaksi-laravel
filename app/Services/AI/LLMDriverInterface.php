<?php

namespace App\Services\AI;

interface LLMDriverInterface
{
    /**
     * Parse text and determine if it's a RECORD, REPORT, or UNKNOWN intent.
     * 
     * @param string $text
     * @return array|null [intent, data|params]
     */
    public function process(string $text): ?array;

    /**
     * Get the driver name.
     */
    public function getName(): string;
}
