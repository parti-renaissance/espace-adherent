<?php

namespace App\Command;

use App\Entity\ProcurationProxy;
use App\Repository\ProcurationProxyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Oneshot command, can be deleted after execution.
 */
class ProcessProcurationProxyElectionRoundAvailabilitiesCommand extends Command
{
    private const BATCH_SIZE = 1000;

    protected static $defaultName = 'app:procuration:process-availabilities';

    private EntityManagerInterface $em;
    private ProcurationProxyRepository $procurationProxyRepository;
    private SymfonyStyle $io;

    public function __construct(EntityManagerInterface $em, ProcurationProxyRepository $procurationProxyRepository)
    {
        $this->em = $em;
        $this->procurationProxyRepository = $procurationProxyRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Process availabilities for ProcurationProxy.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Starting processing ProcurationProxy availabilities.');

        $this->io->progressStart($this->procurationProxyCount());

        $count = 0;
        foreach ($this->getProcurationProxies() as $result) {
            /* @var ProcurationProxy $proxy */
            $proxy = $result[0];
            $proxy->processAvailabilities();
            $this->em->flush();

            if (0 === (++$count % self::BATCH_SIZE)) {
                $this->em->clear();
            }

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        $this->io->success('Processing ProcurationProxy availabilities has been finished successfully.');

        return 0;
    }

    private function getProcurationProxies(): IterableResult
    {
        return $this
            ->createProcurationProxyQueryBuilder()
            ->getQuery()
            ->iterate()
        ;
    }

    private function procurationProxyCount(): int
    {
        return $this
            ->createProcurationProxyQueryBuilder()
            ->select('COUNT(1)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function createProcurationProxyQueryBuilder(): QueryBuilder
    {
        return $this
            ->procurationProxyRepository
            ->createQueryBuilder('pp')
        ;
    }
}
