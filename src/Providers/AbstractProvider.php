<?php

declare(strict_types=1);

namespace App\Providers;

use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class AbstractProvider
{
    static protected string $baseUrl;

    protected HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $defaultOptions = [
            'base_uri' => static::$baseUrl,
        ];

        $this->client = $client->withOptions(array_merge($defaultOptions, $this->getOptions()));
    }

    public function getOptions(): array
    {
        return [];
    }
}