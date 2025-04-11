<?php

namespace App\Command;

use App\Entity\Event\Event;
use App\Entity\Event\EventRegistration;
use App\Event\Command\SendEmailReminderCommand;
use App\Repository\Event\EventRepository;
use App\Repository\EventRegistrationRepository;
use App\Subscription\SubscriptionTypeEnum;
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
            $eventRegistrations = $this->getEventRegistrations($event);

            foreach ($eventRegistrations as $eventRegistration) {
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
            ->leftJoin('r.adherent', 'a')
            ->leftJoin('a.subscriptionTypes', 'subscription_type')
            ->where('r.event = :event')
            ->andWhere('adherent IS NULL OR subscription_type.code = :subscription_type_code')
            ->andWhere(
                $qb->expr()->orX()
                    ->add('r.adherent IS NULL')
                    ->add('subscription_type.code = :subscription_type_code')
            )
            ->setParameters([
                'event' => $event,
                'subscription_type_code' => SubscriptionTypeEnum::EVENT_EMAIL,
            ])
            ->getQuery()
            ->getResult()
        ;
    }
}
