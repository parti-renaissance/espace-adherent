<?php

namespace App\Command;

use App\Entity\CitizenProjectMembership;
use App\Entity\CommitteeMembership;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Mailchimp\Synchronisation\Command\AddAdherentToStaticSegmentCommand;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class MailchimpSyncAllMembershipsCommand extends Command
{
    protected static $defaultName = 'mailchimp:sync:all-memberships';

    private const COMMITTEE_TYPE = 'committee';
    private const CITIZEN_PROJECT_TYPE = 'citizen_project';
    private const TERRITORIAL_COUNCIL_TYPE = 'territorial_council';

    private static $allTypes = [
        self::COMMITTEE_TYPE,
        self::CITIZEN_PROJECT_TYPE,
        self::TERRITORIAL_COUNCIL_TYPE,
    ];

    private $entityManager;
    private $bus;
    /** @var SymfonyStyle */
    private $io;

    public function __construct(ObjectManager $entityManager, MessageBusInterface $bus)
    {
        $this->entityManager = $entityManager;
        $this->bus = $bus;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument('type', null, InputArgument::REQUIRED, implode('|', static::$allTypes))
            ->addOption('limit', null, InputOption::VALUE_REQUIRED)
            ->addOption('object-id', null, InputOption::VALUE_REQUIRED)
        ;
    }

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getArgument('type');

        if (!\in_array($type, static::$allTypes)) {
            throw new \InvalidArgumentException('Type value is invalid');
        }

        $limit = (int) $input->getOption('limit');
        $objectId = $input->getOption('object-id');

        $paginator = $this->buildPaginator($type, $objectId);

        $count = $paginator->count();
        $total = $limit && $limit < $count ? $limit : $count;

        if (false === $this->io->confirm(sprintf('Are you sure to sync %d adherents?', $total), false)) {
            return 1;
        }

        $paginator->getQuery()->setMaxResults($limit && $limit < 500 ? $limit : 500);

        $this->io->progressStart($total);
        $offset = 0;

        do {
            /** @var CommitteeMembership|CitizenProjectMembership|TerritorialCouncilMembership $membership */
            foreach ($paginator->getIterator() as $membership) {
                switch ($type) {
                    case self::COMMITTEE_TYPE: $object = $membership->getCommittee(); break;
                    case self::CITIZEN_PROJECT_TYPE: $object = $membership->getCitizenProject(); break;
                    case self::TERRITORIAL_COUNCIL_TYPE: $object = $membership->getTerritorialCouncil(); break;
                }

                $this->bus->dispatch(new AddAdherentToStaticSegmentCommand(
                    $membership->getAdherent()->getUuid(),
                    $object->getUuid(),
                    \get_class($object)
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
    }

    private function buildPaginator(string $type, int $objectId = null): Paginator
    {
        return new Paginator($this->getQueryBuilder($type, $objectId)->getQuery());
    }

    private function getQueryBuilder(string $type, int $objectId = null): QueryBuilder
    {
        switch ($type) {
            case self::COMMITTEE_TYPE:
                $qb = $this->entityManager
                    ->getRepository(CommitteeMembership::class)
                    ->createQueryBuilder('membership')
                    ->addSelect('PARTIAL adherent.{id, uuid}')
                    ->innerJoin('membership.adherent', 'adherent')
                    ->innerJoin('membership.committee', 'object')
                ;
                break;

            case self::CITIZEN_PROJECT_TYPE:
                $qb = $this->entityManager
                    ->getRepository(CitizenProjectMembership::class)
                    ->createQueryBuilder('membership')
                    ->addSelect('PARTIAL adherent.{id, uuid}')
                    ->innerJoin('membership.adherent', 'adherent')
                    ->innerJoin('membership.citizenProject', 'object')
                ;
                break;

            case self::TERRITORIAL_COUNCIL_TYPE:
                $qb = $this->entityManager
                    ->getRepository(TerritorialCouncilMembership::class)
                    ->createQueryBuilder('membership')
                    ->addSelect('PARTIAL adherent.{id, uuid}')
                    ->innerJoin('membership.adherent', 'adherent')
                    ->innerJoin('membership.territorialCouncil', 'object')
                ;
                break;
        }

        if ($objectId) {
            $qb
                ->andWhere('object.id = :id')
                ->setParameter('id', $objectId)
            ;
        }

        return $qb;
    }
}
