<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Event;
use App\Message\ProcessEvents;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

final readonly class EventsService
{
    private const INSERT_BATCH_SIZE = 100;

    public function __construct(
        private CacheItemPoolInterface $cache,
        private ManagerRegistry $doctrine,
        private MessageBusInterface $messageBus,
    )
    {
    }

    public function processEvents(array $events): void
    {
        $cacheKey = Uuid::v4()->toString();

        $cacheItem = $this->cache->getItem("events:$cacheKey");
        $cacheItem->set($events);

        $this->cache->save($cacheItem);

        $message = new ProcessEvents(cacheKey:  $cacheKey);
        $this->messageBus->dispatch($message);
    }

    public function saveEvents(array $events): void
    {
        // @TODO Replace with plain PDO insert statements
        foreach ($events as $index => $event) {
            $entity = (new  Event())
                ->setName($event['name']);

            $this->doctrine->getManager()->persist($entity);
            if ($index % self::INSERT_BATCH_SIZE === 0) {
                $this->doctrine->getManager()->flush();
                $this->doctrine->getManager()->clear();
            }
        }

        $this->doctrine->getManager()->flush();
        $this->doctrine->getManager()->clear();

        $this->notifyEvents($events);
    }

    public function fetchEvents(): array
    {
        return $this->doctrine
            ->getRepository(Event::class, 'read_only_connection')
            ->findAll(); // fetchLatestEvents()
    }

    public function notifyEvents(array $events): void
    {
        $update = new Update('https://sportevents.com', json_encode($events));

        $this->messageBus->dispatch($update);
    }
}