<?php

namespace App\Command;

use App\Entity\Event\Event;
use App\JeMengage\Push\Command\EventReminderNotificationCommand;
use App\Repository\Event\EventRepository;
use Cake\Chronos\Chronos;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:events:remind',
    description: 'This command finds upcoming events and send reminders',
)]
class EventReminderCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly EventRepository $eventRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('mode', InputArgument::REQUIRED, 'Events mode to filter ("online" or "meeting")')
            ->addOption('online-start-after', 'a', InputOption::VALUE_OPTIONAL, 'Minimum number of minutes before the online events begin', 30)
            ->addOption('online-start-before', 'b', InputOption::VALUE_OPTIONAL, 'Maximum number of minutes before the online events begin', 90)
            ->addOption('meeting-delay', 'd', InputOption::VALUE_OPTIONAL, 'Number of days before reminder is sent to meeting events.', 1)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $mode = $input->getArgument('mode');

        if (Event::MODE_MEETING === $mode) {
            $startAfter = (new Chronos())->modify(\sprintf('+%d days', (int) $input->getOption('meeting-delay')))->setTime(0, 0, 0);
            $startBefore = (clone $startAfter)->modify('+1 day');
        } elseif (Event::MODE_ONLINE === $mode) {
            $startAfter = (new Chronos())->modify(\sprintf('+%d minutes', (int) $input->getOption('online-start-after')));
            $startBefore = (new Chronos())->modify(\sprintf('+%d minutes', (int) $input->getOption('online-start-before')));
        } else {
            throw new \InvalidArgumentException(\sprintf('Event mode "%s" is not defined.', $mode));
        }
        $events = $this->eventRepository->findEventsToRemind($startAfter, $startBefore, $mode);

        $this->io->progressStart($total = \count($events));

        foreach ($events as $event) {
            $this->bus->dispatch(new EventReminderNotificationCommand($event->getUuid()));
            $this->io->progressAdvance();
        }

        $this->io->progressFinish();
        $this->io->success("$total events has been reminded.");

        return self::SUCCESS;
    }
}
