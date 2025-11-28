<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\Event\EventRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Service\Attribute\Required;

#[AsCommand(
    name: 'app:events:update-registrations-counters',
    description: '',
)]
class UpdateEvenRegistrationsCountersCommand extends Command
{
    private EventRepository $eventRepository;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->eventRepository->updateRegistrationsCounters();

        return self::SUCCESS;
    }

    #[Required]
    public function setEventRepository(EventRepository $eventRepository): void
    {
        $this->eventRepository = $eventRepository;
    }
}
