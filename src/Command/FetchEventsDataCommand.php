<?php

declare(strict_types=1);

namespace App\Command;

use App\Contracts\ProviderInterface;
use App\Providers\SportApiServiceProvider;
use App\Providers\SportXmlServiceProvider;
use App\Services\EventsService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:fetch-events', description: 'Fetch events data from providers')]
final class FetchEventsDataCommand extends Command
{
    /**
     * @var ProviderInterface[]
     */
    private array $providers = [
        SportXmlServiceProvider::class,
        SportApiServiceProvider::class,
    ];

    public function __construct(
        private readonly EventsService $eventsService,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach ($this->providers as $provider) {
            $events = $provider->toArray($provider->fetch());

            $this->eventsService->processEvents($events);

            $io->note('Processing events for provider: '.$provider::class);
        }

        return Command::SUCCESS;
    }
}
