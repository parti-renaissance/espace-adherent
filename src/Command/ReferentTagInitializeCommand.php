<?php

namespace AppBundle\Command;

use AppBundle\Entity\Adherent;
use AppBundle\Referent\ReferentTagManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReferentTagInitializeCommand extends Command
{
    private const BATCH_SIZE = 250;

    private $messageCount;
    private $em;
    private $tagManager;

    public function __construct(EntityManagerInterface $em, ReferentTagManager $tagManager)
    {
        $this->em = $em;
        $this->tagManager = $tagManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:referent-tags:initialize')
            ->addArgument('batchSize', InputArgument::OPTIONAL)
            ->setDescription('Initialize Referent Tags for Adherents')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(['', 'Starting Referent Tags initialization.']);

        $progressBar = new ProgressBar($output, $this->getCount());

        $this->em->beginTransaction();

        foreach ($this->getIterator() as $result) {
            $adherent = reset($result);

            $this->tagManager->assignAdherentLocalTag($adherent);

            $progressBar->advance();

            ++$this->messageCount;

            if (0 === ($this->messageCount % self::BATCH_SIZE)) {
                $this->em->flush();
                $this->em->clear();
                $this->messageCount = 0;
            }
        }

        $this->em->flush();
        $this->em->commit();

        $progressBar->finish();

        $output->writeln(['', 'Referent Tags initialized successfully!']);
    }

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
            ->select('COUNT(a)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function getQueryBuilder(): QueryBuilder
    {
        return $this
            ->em
            ->getRepository(Adherent::class)
            ->createQueryBuilder('a')
        ;
    }
}
