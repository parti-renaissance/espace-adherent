<?php

namespace AppBundle\Command;

use AppBundle\Entity\Event;
use AppBundle\Event\EventEvent;
use AppBundle\Events;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ApiSynchronizeEventsCommand extends Command
{
    private $em;
    private $dispatcher;

    public function __construct(
        EntityManagerInterface $em,
        EventDispatcherInterface $dispatcher
    ) {
        $this->em = $em;
        $this->dispatcher = $dispatcher;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:sync:events')
            ->setDescription('Schedule Events for synchronization with api')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(['', 'Starting Events synchronization.']);

        foreach ($this->getEventIterator() as $result) {
            $this->scheduleEventSynchronization(reset($result));
        }

        $output->writeln(['', 'Events successfully scheduled for synchronization!']);
    }

    private function getEventIterator(): IterableResult
    {
        return $this
            ->em
            ->getRepository(Event::class)
            ->createQueryBuilder('e')
            ->getQuery()
            ->iterate()
        ;
    }

    private function scheduleEventSynchronization(Event $event): void
    {
        $this->dispatcher->dispatch(Events::EVENT_CREATED, new EventEvent(null, $event));
    }
}
