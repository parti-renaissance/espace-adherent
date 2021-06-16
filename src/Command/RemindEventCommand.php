<?php

namespace App\Command;

use App\Entity\Event\BaseEvent;
use App\Event\EventReminderHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemindEventCommand extends Command
{
    protected static $defaultName = 'app:events:remind';

    private $handler;

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
            ->addArgument('online-start-after', InputArgument::OPTIONAL, 'Minimum number of minutes before the online events begin')
            ->addArgument('online-start-before', InputArgument::OPTIONAL, 'Maximum number of minutes before the online events begin')
            ->addArgument('meeting-delay', InputArgument::OPTIONAL, 'Number of days before reminder is sent to meeting events.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mode = $input->getArgument('mode');

        if (BaseEvent::MODE_MEETING === $mode) {
            $startAfter = (new \DateTime())->modify(sprintf('+%d days', (int) $input->getArgument('meeting-delay')))->setTime(0, 0, 0);
            $startBefore = (clone $startAfter)->modify('+1 day');
        } elseif (BaseEvent::MODE_ONLINE === $mode) {
            $startAfter = (new \DateTime())->modify(sprintf('+%d minutes', (int) $input->getArgument('online-start-after')));
            $startBefore = (new \DateTime())->modify(sprintf('+%d minutes', (int) $input->getArgument('online-start-before')));
        } else {
            throw new \InvalidArgumentException(sprintf('Event mode "%s" is not defined.', $mode));
        }

        foreach ($this->handler->findEventsToRemind($startAfter, $startBefore, $mode) as $event) {
            $this->handler->scheduleReminder($event);
        }
    }
}
