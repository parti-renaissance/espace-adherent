<?php

namespace App\Command;

use App\Entity\SynchronizedEntity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class ApiScheduleEntityCreationCommand extends Command
{
    private const BATCH_SIZE = 200;

    protected $dispatcher;
    private $em;
    private $messageCount = 0;

    public function __construct(EntityManagerInterface $em, EventDispatcherInterface $dispatcher)
    {
        $this->em = $em;
        $this->dispatcher = $dispatcher;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(['', 'Starting synchronization.']);

        $progressBar = new ProgressBar($output, $this->getCount());

        foreach ($this->getIterator() as $result) {
            $this->scheduleCreation(reset($result));

            $progressBar->advance();

            ++$this->messageCount;

            if (0 === ($this->messageCount % self::BATCH_SIZE)) {
                $this->em->clear();
                $this->messageCount = 0;
            }
        }

        $progressBar->finish();

        $output->writeln(['', 'Successfully scheduled for synchronization!']);
    }

    abstract protected function getEntityClassname(): string;

    abstract protected function scheduleCreation(SynchronizedEntity $entity): void;

    private function getIterator(): IterableResult
    {
        return $this
            ->getQueryBuilder()
            ->getQuery()
            ->iterate()
        ;
    }

    private function getCount(): int
    {
        return $this
            ->getQueryBuilder()
            ->select('COUNT(e)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function getQueryBuilder(): QueryBuilder
    {
        return $this
            ->em
            ->getRepository($this->getEntityClassname())
            ->createQueryBuilder('e')
        ;
    }
}
