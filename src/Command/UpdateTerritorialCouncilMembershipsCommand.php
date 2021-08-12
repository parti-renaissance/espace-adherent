<?php

namespace App\Command;

use App\Entity\Adherent;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Repository\AdherentRepository;
use App\TerritorialCouncil\Command\AdherentUpdateTerritorialCouncilMembershipsCommand;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class UpdateTerritorialCouncilMembershipsCommand extends Command
{
    protected static $defaultName = 'app:territorial-council:update-memberships';

    private const BATCH_SIZE = 1000;

    /** @var AdherentRepository */
    private $adherentRepository;
    /** @var EntityManagerInterface */
    private $em;
    /** @var MessageBusInterface */
    private $bus;
    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        MessageBusInterface $bus,
        EntityManagerInterface $em,
        AdherentRepository $adherentRepository
    ) {
        $this->em = $em;
        $this->bus = $bus;
        $this->adherentRepository = $adherentRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_REQUIRED)
            ->addOption('disable-mailchimp-sync', null, InputOption::VALUE_NONE)
            ->addOption('only-elected-representatives', null, InputOption::VALUE_NONE)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->text('Start updating territorial council memberships');

        $limit = (int) $input->getOption('limit');
        $disableMailchimpSync = $input->getOption('disable-mailchimp-sync');
        $onlyElectedRepresentatives = $input->getOption('only-elected-representatives');

        $paginator = $this->getAdherentsPaginator($onlyElectedRepresentatives);

        $count = $paginator->count();
        $total = $limit && $limit < $count ? $limit : $count;

        $paginator->getQuery()->setMaxResults($limit && $limit < self::BATCH_SIZE ? $limit : self::BATCH_SIZE);

        $this->io->progressStart($total);
        $offset = 0;

        do {
            foreach ($paginator->getIterator() as $adherent) {
                $command = new AdherentUpdateTerritorialCouncilMembershipsCommand(
                    $adherent->getUuid(),
                    !$disableMailchimpSync
                );
                try {
                    $this->bus->dispatch($command);
                } catch (\AMQPException $exception) {
                    $this->bus->dispatch($command);
                }

                $this->io->progressAdvance();
                ++$offset;

                if ($limit && $limit <= $offset) {
                    break 2;
                }
            }

            $paginator->getQuery()->setFirstResult($offset);

            $this->em->clear();
        } while ($offset < $count && (!$limit || $offset < $limit));

        $this->io->progressFinish();
        $this->io->success('Done');

        return 0;
    }

    private function getAdherentsPaginator(bool $onlyElectedRepresentatives = false): Paginator
    {
        $queryBuilder = $this->adherentRepository
            ->createQueryBuilder('adherent')
            ->select('PARTIAL adherent.{id, uuid}')
            ->where('adherent.status = :status')
            ->andWhere('adherent.adherent = true')
            ->setParameter('status', Adherent::ENABLED)
        ;

        if ($onlyElectedRepresentatives) {
            $queryBuilder
                ->leftJoin(ElectedRepresentative::class, 'er', Join::WITH, 'er.adherent = adherent')
                ->andWhere('er.id IS NOT NULL')
            ;
        }

        return new Paginator($queryBuilder->getQuery());
    }
}
