<?php

declare(strict_types=1);

namespace App\Contracts;

interface ProviderInterface
{


    public function fetch(): array;

    public function toArray(array $data): array;

    public function getOptions(): array;
}