<?php

declare(strict_types=1);

namespace App\Message;

final readonly class ProcessEvents
{
    public function __construct(private string $cacheKey)
    {
    }

    /**
     * @return string
     */
    public function getCacheKey(): string
    {
        return $this->cacheKey;
    }
}
