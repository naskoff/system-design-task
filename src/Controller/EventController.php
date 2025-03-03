<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\EventsService;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class EventController extends AbstractController
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly LoggerInterface $logger,
        private readonly EventsService $eventsService,
    )
    {
    }

    public function list(Request $request): Response
    {
        $cacheKey = hash('sha1', $request->getUri());

        try {
            $events = $this->cache->get($cacheKey, function (ItemInterface $item) use ($request) {
                $item->expiresAfter(30);

                return $this->eventsService->fetchEvents();
            });
        } catch (InvalidArgumentException $e) {
            $this->logger->critical('Error fetching events', ['exception' => $e]);

            $events = [];
        }

        return $this->json($events, Response::HTTP_OK);
    }

    #[Route('/events', methods: [Request::METHOD_POST])]
    public function store(Request $request): Response
    {
        $events = $request->toArray();

        $this->eventsService->processEvents($events);

        return $this->json(null, Response::HTTP_CREATED);
    }
}
