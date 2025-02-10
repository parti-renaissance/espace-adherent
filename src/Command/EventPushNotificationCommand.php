<?php

namespace App\Command;

use App\JeMengage\Push\Command\EventLiveBeginNotificationCommand;
use App\Repository\Event\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:events:push',
    description: 'This command finds upcoming events and send push notification',
)]
class EventPushNotificationCommand extends Command
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly EventRepository $eventRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $events = $this->eventRepository->findWithLiveToNotify();

        foreach ($events as $event) {
            $this->bus->dispatch(new EventLiveBeginNotificationCommand($event->getUuid()));
            $event->pushSentAt = new \DateTime();

            $this->entityManager->flush();
        }

        return self::SUCCESS;
    }
}
