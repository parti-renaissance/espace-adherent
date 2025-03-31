<?php

namespace App\Command;

use App\NationalEvent\Command\SendReminderCommand;
use App\Repository\NationalEvent\EventInscriptionRepository;
use App\Repository\NationalEvent\NationalEventRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:national-event:remind',
    description: 'This command finds upcoming national events and send reminders',
)]
class NationalEventReminderCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly NationalEventRepository $nationalEventRepository,
        private readonly EventInscriptionRepository $eventInscriptionRepository,
    ) {
        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startAfter = (new \DateTime())->setTime(0, 0, 0);
        $startBefore = (new \DateTime())->setTime(23, 59, 59);

        $events = $this->nationalEventRepository->findUpcoming($startAfter, $startBefore);

        $this->io->progressStart($total = \count($events));

        foreach ($events as $event) {
            $eventInscriptions = $this->eventInscriptionRepository->findAllPartialForEvent($event, []);

            foreach ($eventInscriptions as $eventInscription) {
                $this->bus->dispatch(new SendReminderCommand($eventInscription->getUuid()));
            }

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();
        $this->io->success("$total events has been reminded.");

        return self::SUCCESS;
    }
}
