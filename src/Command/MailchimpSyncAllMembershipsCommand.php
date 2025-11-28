<?php

namespace App\Command;

use App\Entity\CommitteeMembership;
use App\Mailchimp\Synchronisation\Command\AddAdherentToStaticSegmentCommand;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'mailchimp:sync:all-memberships',
)]
class MailchimpSyncAllMembershipsCommand extends Command
{
    private const COMMITTEE_TYPE = 'committee';

    private static $allTypes = [
        self::COMMITTEE_TYPE,
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

    protected function configure(): void
    {
        $this
            ->addArgument('type', InputArgument::REQUIRED, implode('|', static::$allTypes))
            ->addOption('limit', null, InputOption::VALUE_REQUIRED)
            ->addOption('object-id', null, InputOption::VALUE_REQUIRED)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
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

        if (false === $this->io->confirm(\sprintf('Are you sure to sync %d adherents?', $total), false)) {
            return self::FAILURE;
        }

        $paginator->getQuery()->setMaxResults($limit && $limit < 500 ? $limit : 500);

        $this->io->progressStart($total);
        $offset = 0;

        do {
            /** @var CommitteeMembership $membership */
            foreach ($paginator->getIterator() as $membership) {
                $object = null;

                switch ($type) {
                    case self::COMMITTEE_TYPE: $object = $membership->getCommittee();
                        break;
                }

                if ($object) {
                    $this->bus->dispatch(new AddAdherentToStaticSegmentCommand(
                        $membership->getAdherent()->getUuid(),
                        $object->getUuid(),
                        $object::class
                    ));
                }

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

    private function buildPaginator(string $type, ?int $objectId = null): Paginator
    {
        return new Paginator($this->getQueryBuilder($type, $objectId)->getQuery());
    }

    private function getQueryBuilder(string $type, ?int $objectId = null): QueryBuilder
    {
        $qb = null;

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
