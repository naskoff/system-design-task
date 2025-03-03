<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\ProviderInterface;
use Symfony\Component\HttpFoundation\Request;

final class SportApiServiceProvider extends AbstractProvider implements ProviderInterface
{
    static protected string $baseUrl = 'https://api.github.com/';

    public function fetch(): array
    {
        $response = $this->client
            ->request(Request::METHOD_GET, '/events', [
                'auth_bearer' => 'token',
                'headers' => [
                    'Accept' => 'application/vnd.github.v3+json',
                ],
            ]);

        return $response->toArray();
    }

    public function toArray(array $data): array
    {
        return array_map(fn(array $item) => [
            'id' => $item['event_id'],
            'name' => $item['event_name'],
        ], $data['items']);
    }

}