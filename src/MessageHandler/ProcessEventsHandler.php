<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\ProcessEvents;
use App\Services\EventsService;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ProcessEventsHandler
{
    public function __construct(
        private CacheItemPoolInterface $cache,
        private EventsService $eventsService,
        private LoggerInterface $logger,
    )
    {
    }

    public function __invoke(ProcessEvents $message): void
    {
        $cacheKey = "events:{$message->getCacheKey()}";

        $cacheData = $this->cache->getItem($cacheKey);

        if (!$cacheData->isHit()) {
            $this->logger->critical('Events data not found', [
                'cacheKey' => $cacheKey,
            ]);

            return;
        }

        $events = $cacheData->get();

        $this->eventsService->saveEvents($events);

        $this->cache->deleteItem($cacheKey);
    }
}
