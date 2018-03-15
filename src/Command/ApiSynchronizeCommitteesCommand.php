<?php

namespace AppBundle\Command;

use AppBundle\Committee\CommitteeEvent;
use AppBundle\Entity\Committee;
use AppBundle\Events;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ApiSynchronizeCommitteesCommand extends Command
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
            ->setName('app:sync:committees')
            ->setDescription('Schedule Committees for synchronization with api')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(['', 'Starting Committees synchronization.']);

        foreach ($this->getCommitteeIterator() as $result) {
            $this->scheduleCommitteeSynchronization(reset($result));
        }

        $output->writeln(['', 'Committees successfully scheduled for synchronization!']);
    }

    private function getCommitteeIterator(): IterableResult
    {
        return $this
            ->em
            ->getRepository(Committee::class)
            ->createQueryBuilder('c')
            ->getQuery()
            ->iterate()
        ;
    }

    private function scheduleCommitteeSynchronization(Committee $committee): void
    {
        $this->dispatcher->dispatch(Events::COMMITTEE_CREATED, new CommitteeEvent($committee));
    }
}
