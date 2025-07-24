<?php

namespace App\Command;

use App\Entity\Event\Event;
use App\Entity\Event\EventRegistration;
use App\Event\Command\SendEmailReminderCommand;
use App\Repository\Event\EventRepository;
use App\Repository\EventRegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:event:send-email-reminder',
    description: 'This command finds upcoming events and send email reminders',
)]
class SendEventEmailReminderCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly EventRepository $eventRepository,
        private readonly EventRegistrationRepository $eventRegistrationRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startAfter = (new \DateTime())->setTime(7, 0);
        $startBefore = (clone $startAfter)->modify('+1 day');

        $events = $this->eventRepository->findEventsToRemindByEmail($startAfter, $startBefore);

        $this->io->progressStart($total = \count($events));

        foreach ($events as $event) {
            foreach ($this->getEventRegistrations($event) as $eventRegistration) {
                $this->bus->dispatch(new SendEmailReminderCommand($eventRegistration->getUuid()));
            }

            $event->setEmailReminded(true);

            $this->entityManager->flush();

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();
        $this->io->success("$total events has been reminded.");

        return self::SUCCESS;
    }

    /** @return EventRegistration[] */
    private function getEventRegistrations(Event $event): array
    {
        $qb = $this->eventRegistrationRepository->createQueryBuilder('r');

        return $qb
            ->select('PARTIAL r.{id,uuid}')
            ->innerJoin('r.event', 'e')
            ->where('r.event = :event')
            ->andWhere('r.emailAddress IS NOT NULL')
            ->andWhere('e.emailReminded = :false')
            ->setParameter('event', $event)
            ->setParameter('false', false)
            ->getQuery()
            ->getResult()
        ;
    }
}
