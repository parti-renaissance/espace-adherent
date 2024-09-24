<?php

namespace App\Command;

use App\Adherent\Tag\Command\AsyncRefreshAdherentTagCommand;
use App\Entity\Adherent;
use App\Entity\ProcurationV2\Proxy;
use App\Entity\ProcurationV2\Request;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand('app:adherent:refresh-tags')]
class RefreshAdherentsTagsCommand extends Command
{
    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly ObjectManager $entityManager,
        private readonly MessageBusInterface $bus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('id', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY)
            ->addOption('email', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY)
            ->addOption('source', null, InputOption::VALUE_REQUIRED)
            ->addOption('procuration-only', null, InputOption::VALUE_NONE, 'Only refresh adherents linked to procurations')
            ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, '', 500)
            ->addOption('dry-run', null, InputOption::VALUE_NONE)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $batchSize = $input->getOption('batch-size');

        $paginator = $this->getQueryBuilder(
            $input->getOption('id'),
            $input->getOption('email'),
            $input->getOption('source'),
            $input->getOption('procuration-only')
        );

        $total = $paginator->count();

        if (!$this->io->isQuiet() && false === $this->io->confirm(\sprintf('Are you sure to sync %d adherents?', $total), false)) {
            return self::FAILURE;
        }

        $paginator->getQuery()->setMaxResults($batchSize);

        $this->io->progressStart($total);
        $offset = 0;

        do {
            foreach ($paginator as $adherent) {
                if (!$input->getOption('dry-run')) {
                    $this->bus->dispatch(new AsyncRefreshAdherentTagCommand($adherent->getUuid()));
                }

                $this->io->progressAdvance();
                ++$offset;
            }

            $paginator->getQuery()->setFirstResult($offset);

            $this->entityManager->clear();
        } while ($offset < $total);

        $this->io->progressFinish();

        return self::SUCCESS;
    }

    /**
     * @return Paginator|Adherent[]
     */
    private function getQueryBuilder(array $ids, array $emails, ?string $source, bool $procurationsOnly): Paginator
    {
        $queryBuilder = $this->adherentRepository
            ->createQueryBuilder('adherent')
            ->select('PARTIAL adherent.{id, uuid}')
        ;

        if ($ids) {
            $queryBuilder
                ->andWhere('adherent.id IN (:ids)')
                ->setParameter('ids', $ids)
            ;
        }

        if ($emails) {
            $queryBuilder
                ->andWhere('adherent.emailAddress IN (:emails)')
                ->setParameter('emails', $emails)
            ;
        }

        if ($source) {
            $queryBuilder
                ->andWhere('adherent.source = :source')
                ->setParameter('source', $source)
            ;
        }

        if ($procurationsOnly) {
            $queryBuilder
                ->leftJoin(Proxy::class, 'procuration_proxy', Join::WITH, 'adherent = procuration_proxy.adherent')
                ->leftJoin(Request::class, 'procuration_request', Join::WITH, 'adherent = procuration_request.adherent')
                ->andWhere('procuration_proxy IS NOT NULL OR procuration_request IS NOT NULL')
            ;
        }

        return new Paginator($queryBuilder->getQuery());
    }
}
