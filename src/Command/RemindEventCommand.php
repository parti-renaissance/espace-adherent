<?php

namespace App\Command;

use App\Entity\Event\BaseEvent;
use App\Event\EventReminderHandler;
use Cake\Chronos\Chronos;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RemindEventCommand extends Command
{
    protected static $defaultName = 'app:events:remind';

    private $handler;

    /** @var SymfonyStyle */
    private $io;

    public function __construct(EventReminderHandler $handler)
    {
        parent::__construct();

        $this->handler = $handler;
    }

    protected function configure()
    {
        $this
            ->setDescription('This command finds upcoming events and send reminders')
            ->addArgument('mode', InputArgument::REQUIRED, 'Events mode to filter ("online" or "meeting")')
            ->addOption('online-start-after', 'a', InputOption::VALUE_OPTIONAL, 'Minimum number of minutes before the online events begin', 30)
            ->addOption('online-start-before', 'b', InputOption::VALUE_OPTIONAL, 'Maximum number of minutes before the online events begin', 90)
            ->addOption('meeting-delay', 'd', InputOption::VALUE_OPTIONAL, 'Number of days before reminder is sent to meeting events.', 1)
        ;
    }

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mode = $input->getArgument('mode');

        if (BaseEvent::MODE_MEETING === $mode) {
            $startAfter = (new Chronos())->modify(sprintf('+%d days', (int) $input->getOption('meeting-delay')))->setTime(0, 0, 0);
            $startBefore = (clone $startAfter)->modify('+1 day');
        } elseif (BaseEvent::MODE_ONLINE === $mode) {
            $startAfter = (new Chronos())->modify(sprintf('+%d minutes', (int) $input->getOption('online-start-after')));
            $startBefore = (new Chronos())->modify(sprintf('+%d minutes', (int) $input->getOption('online-start-before')));
        } else {
            throw new \InvalidArgumentException(sprintf('Event mode "%s" is not defined.', $mode));
        }

        $events = $this->handler->findEventsToRemind($startAfter, $startBefore, $mode);

        $this->io->progressStart($total = \count($events));

        foreach ($events as $event) {
            $this->handler->scheduleReminder($event);
            $this->io->progressAdvance();
        }

        $this->io->progressFinish();
        $this->io->success("$total events has been reminded.");

        return 0;
    }
}
