<?php

namespace App\Services\AI;

interface LLMDriverInterface
{
    /**
     * Parse text into structured transaction data.
     * 
     * @param string $text
     * @return array|null [type, amount, category, description]
     */
    public function parse(string $text): ?array;

    /**
     * Get the driver name.
     */
    public function getName(): string;
}
