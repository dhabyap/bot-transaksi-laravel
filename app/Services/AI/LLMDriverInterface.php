<?php

namespace App\Services\AI;

interface LLMDriverInterface
{
    /**
     * Parse text and determine if it's a RECORD, REPORT, or UNKNOWN intent.
     * 
     * @param string $text
     * @param array $context [categories, current_date, etc.]
     * @return array|null [intent, data|params]
     */
    public function process(string $text, array $context = []): ?array;

    /**
     * Get the driver name.
     */
    public function getName(): string;
}
