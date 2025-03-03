<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\ProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class SportXmlServiceProvider extends AbstractProvider implements ProviderInterface
{
    static protected string $baseUrl = 'https://xml-service.com/';

    private readonly SerializerInterface $serializer;

    public function __construct(
        HttpClientInterface $client,
        SerializerInterface $serializer,
    )
    {
        parent::__construct($client);

        $this->serializer = $serializer;
    }

    public function fetch(): array
    {
        $response = $this->client->request(Request::METHOD_GET, '/events.xml');

        // format xml to json
        return $this->serializer->deserialize(
            data: $response->getContent(),
            type: JsonEncoder::FORMAT,
            format: XmlEncoder::FORMAT,
        );
    }

    public function toArray(array $data): array
    {
        return array_map(fn(array $item) => [
            'id' => $item['id'],
            'name' => $item['name'],
            // process other fields
        ], $data);
    }
}