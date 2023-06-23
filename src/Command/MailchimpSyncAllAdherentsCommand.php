<?php

namespace App\Command;

use App\Campus\RegistrationStatusEnum;
use App\Entity\Adherent;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\Geo\Zone;
use App\Mailchimp\Synchronisation\Command\AdherentChangeCommand;
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

#[AsCommand(
    name: 'mailchimp:sync:all-adherents',
)]
class MailchimpSyncAllAdherentsCommand extends Command
{
    private $adherentRepository;
    private $entityManager;
    private $bus;
    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        AdherentRepository $adherentRepository,
        ObjectManager $entityManager,
        MessageBusInterface $bus
    ) {
        $this->adherentRepository = $adherentRepository;
        $this->entityManager = $entityManager;
        $this->bus = $bus;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_REQUIRED)
            ->addOption('ref-tags', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY)
            ->addOption('zones', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY)
            ->addOption('disabled-only', null, InputOption::VALUE_NONE)
            ->addOption('certified-only', null, InputOption::VALUE_NONE)
            ->addOption('committee-voter-only', null, InputOption::VALUE_NONE)
            ->addOption('active-mandates-only', null, InputOption::VALUE_NONE)
            ->addOption('campus-registered-only', null, InputOption::VALUE_NONE)
            ->addOption('source', null, InputOption::VALUE_REQUIRED)
            ->addOption('emails', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit = (int) $input->getOption('limit');

        $paginator = $this->getQueryBuilder(
            $input->getOption('ref-tags'),
            $input->getOption('zones'),
            $input->getOption('disabled-only'),
            $input->getOption('certified-only'),
            $input->getOption('committee-voter-only'),
            $input->getOption('active-mandates-only'),
            $input->getOption('source'),
            $input->getOption('emails'),
            $input->getOption('campus-registered-only')
        );

        $count = $paginator->count();
        $total = $limit && $limit < $count ? $limit : $count;

        if (false === $this->io->confirm(sprintf('Are you sure to sync %d adherents?', $total), false)) {
            return self::FAILURE;
        }

        $paginator->getQuery()->setMaxResults($limit && $limit < 500 ? $limit : 500);

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
        array $refTags,
        array $zoneCodes,
        bool $disabledOnly,
        bool $certifiedOnly,
        bool $committeeVoterOnly,
        bool $activeMandatesOnly,
        ?string $source,
        array $emails,
        bool $campusRegisteredOnly
    ): Paginator {
        $queryBuilder = $this->adherentRepository
            ->createQueryBuilder('adherent')
            ->where('adherent.status = :status')
            ->setParameter('status', $disabledOnly ? Adherent::DISABLED : Adherent::ENABLED)
        ;

        if ($source) {
            $queryBuilder
                ->andWhere('adherent.source = :source')
                ->setParameter('source', $source)
            ;
        } else {
            $queryBuilder
                ->andWhere('adherent.adherent = true')
                ->andWhere('adherent.source IS NULL')
            ;
        }

        if ($refTags) {
            $queryBuilder
                ->innerJoin('adherent.referentTags', 'tag')
                ->andWhere('tag.code IN (:tags)')
                ->setParameter('tags', $refTags)
            ;
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

        if ($certifiedOnly) {
            $queryBuilder->andWhere('adherent.certifiedAt IS NOT NULL');
        }

        if ($committeeVoterOnly) {
            $queryBuilder
                ->innerJoin(
                    'adherent.memberships',
                    'membership',
                    Join::WITH,
                    'membership.adherent = adherent AND membership.enableVote IS NOT NULL'
                )
            ;
        }
        if ($campusRegisteredOnly) {
            $queryBuilder
                ->innerJoin(
                    'adherent.campusRegistrations',
                    'campus_registration',
                    Join::WITH,
                    'campus_registration.status IN (:campus_registration_valid_status)'
                )
                ->setParameter('campus_registration_valid_status', [
                    RegistrationStatusEnum::INVITED,
                    RegistrationStatusEnum::REGISTERED,
                ])
            ;
        }

        if ($activeMandatesOnly) {
            $queryBuilder
                ->innerJoin(
                    ElectedRepresentative::class,
                    'elected_representative',
                    Join::WITH,
                    'elected_representative.adherent = adherent.id'
                )
                ->innerJoin(
                    'elected_representative.mandates',
                    'mandate',
                    Join::WITH,
                    '(mandate.finishAt IS NULL OR mandate.finishAt > :now) AND mandate.onGoing = 1 AND mandate.isElected = 1'
                )
                ->setParameter('now', new \DateTime())
            ;
        }

        if ($emails) {
            $queryBuilder
                ->andWhere('adherent.emailAddress IN (:emails)')
                ->setParameter('emails', $emails)
            ;
        }

        return new Paginator($queryBuilder->getQuery());
    }
}
