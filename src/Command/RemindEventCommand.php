<?php

namespace App\Command;

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
            ->addArgument('start-after', InputArgument::REQUIRED, 'Minimum number of minutes before the events begin')
            ->addArgument('start-before', InputArgument::REQUIRED, 'Maximum number of minutes before the events begin')
            ->addArgument('mode', InputArgument::OPTIONAL, 'Events mode to filter ("online" or "meeting")')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $startAfter = (new \DateTime())->modify(sprintf('+%d minutes', (int) $input->getArgument('start-after')));
        $startBefore = (new \DateTime())->modify(sprintf('+%d minutes', (int) $input->getArgument('start-before')));
        $mode = $input->getArgument('mode');

        foreach ($this->handler->findEventsToRemind($startAfter, $startBefore, $mode) as $event) {
            $this->handler->scheduleReminder($event);
        }
    }
}
