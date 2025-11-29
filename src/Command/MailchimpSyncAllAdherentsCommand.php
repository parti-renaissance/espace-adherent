<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Adherent;
use App\Entity\AdherentMandate\ElectedRepresentativeAdherentMandate;
use App\Entity\Donator;
use App\Entity\Geo\Zone;
use App\Mailchimp\Synchronisation\Command\AdherentChangeCommand;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(name: 'mailchimp:sync:all-adherents')]
class MailchimpSyncAllAdherentsCommand extends Command
{
    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $bus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_REQUIRED)
            ->addOption('tags', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY)
            ->addOption('zones', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY)
            ->addOption('disabled-only', null, InputOption::VALUE_NONE)
            ->addOption('with-error-only', null, InputOption::VALUE_NONE)
            ->addOption('certified-only', null, InputOption::VALUE_NONE)
            ->addOption('donator-only', null, InputOption::VALUE_NONE)
            ->addOption('committee-voter-only', null, InputOption::VALUE_NONE)
            ->addOption('active-mandates-only', null, InputOption::VALUE_NONE)
            ->addOption('declared-mandates-only', null, InputOption::VALUE_NONE)
            ->addOption('source', null, InputOption::VALUE_REQUIRED)
            ->addOption('emails', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY)
            ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, '', 500)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $batchSize = (int) $input->getOption('batch-size');
        $limit = (int) $input->getOption('limit');

        $paginator = $this->getQueryBuilder(
            $input->getOption('tags'),
            $input->getOption('zones'),
            $input->getOption('disabled-only'),
            $input->getOption('with-error-only'),
            $input->getOption('certified-only'),
            $input->getOption('donator-only'),
            $input->getOption('committee-voter-only'),
            $input->getOption('active-mandates-only'),
            $input->getOption('declared-mandates-only'),
            $input->getOption('source'),
            $input->getOption('emails')
        );

        $count = $paginator->count();
        $total = $limit && $limit < $count ? $limit : $count;

        if (false === $this->io->confirm(\sprintf('Are you sure to sync %d adherents?', $total), false)) {
            return self::FAILURE;
        }

        $paginator->getQuery()->setMaxResults($limit && $limit < $batchSize ? $limit : $batchSize);

        $this->io->progressStart($total);
        $offset = 0;

        do {
            foreach ($paginator as $adherent) {
                $this->bus->dispatch(new AdherentChangeCommand(
                    $adherent->getUuid(),
                    $adherent->getEmailAddress()
                ));
                $this->io->progressAdvance();
                ++$offset;
                if ($limit && $limit <= $offset) {
                    break 2;
                }
            }

            $paginator->getQuery()->setFirstResult($offset);

            $this->entityManager->clear();
        } while ($offset < $count && (!$limit || $offset < $limit));

        $this->io->progressFinish();

        return self::SUCCESS;
    }

    /**
     * @return Paginator|Adherent[]
     */
    private function getQueryBuilder(
        array $tags,
        array $zoneCodes,
        bool $disabledOnly,
        bool $withErrorOnly,
        bool $certifiedOnly,
        bool $donatorOnly,
        bool $committeeVoterOnly,
        bool $activeMandatesOnly,
        bool $declaredMandatesOnly,
        ?string $source,
        array $emails,
    ): Paginator {
        $queryBuilder = $this->adherentRepository
            ->createQueryBuilder('adherent')
            ->select('PARTIAL adherent.{id, uuid, emailAddress}')
            ->where('adherent.status = :status')
            ->setParameter('status', $disabledOnly ? Adherent::DISABLED : Adherent::ENABLED)
        ;

        if ($source) {
            $queryBuilder
                ->andWhere('adherent.source = :source')
                ->setParameter('source', $source)
            ;
        }

        if ($tags) {
            $orX = $queryBuilder->expr()->orX();
            foreach ($tags as $tag) {
                $orX->add('adherent.tags LIKE :tag_'.$tag);
                $queryBuilder->setParameter('tag_'.$tag, '%'.$tag.'%');
            }
            $queryBuilder->andWhere($orX);
        }

        if ($zoneCodes) {
            if ($zones = $this->entityManager->getRepository(Zone::class)->findBy(['code' => $zoneCodes])) {
                $this->io->note('Search in : '.implode(', ', array_map(fn (Zone $zone) => $zone->getTypeCode(), $zones)));

                $this->adherentRepository->withGeoZones(
                    $zones,
                    $queryBuilder,
                    'adherent',
                    Adherent::class,
                    'a2',
                    'zones',
                    'z2'
                );
            }
        }

        if ($withErrorOnly) {
            $queryBuilder->andWhere('adherent.lastMailchimpFailedSyncResponse IS NOT NULL');
        }

        if ($certifiedOnly) {
            $queryBuilder->andWhere('adherent.certifiedAt IS NOT NULL');
        }

        if ($donatorOnly) {
            $queryBuilder->innerJoin(Donator::class, 'donator', Join::WITH, 'donator.adherent = adherent');
        }

        if ($committeeVoterOnly) {
            $queryBuilder
                ->innerJoin(
                    'adherent.committeeMembership',
                    'membership',
                    Join::WITH,
                    'membership.adherent = adherent AND membership.enableVote IS NOT NULL'
                )
            ;
        }

        if ($activeMandatesOnly) {
            $queryBuilder
                ->innerJoin(
                    ElectedRepresentativeAdherentMandate::class,
                    'er_mandate',
                    Join::WITH,
                    'er_mandate.adherent = adherent.id AND er_mandate.finishAt IS NULL'
                )
            ;
        }

        if ($declaredMandatesOnly) {
            $queryBuilder->andWhere('adherent.mandates IS NOT NULL');
        }

        if ($emails) {
            $queryBuilder
                ->andWhere('adherent.emailAddress IN (:emails)')
                ->setParameter('emails', $emails)
            ;
        }

        return new Paginator($queryBuilder->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true));
    }
}
