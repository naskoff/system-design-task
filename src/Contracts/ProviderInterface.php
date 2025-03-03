<?php

declare(strict_types=1);

namespace App\Contracts;

interface ProviderInterface
{
    public function getOptions(): array;
    
    public function fetch(): array;

    public function toArray(array $data): array;
}