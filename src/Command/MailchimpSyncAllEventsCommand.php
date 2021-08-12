<?php

namespace App\Command;

use App\AdherentMessage\Command\CreateStaticSegmentCommand;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class MailchimpSyncAllEventsCommand extends Command
{
    protected static $defaultName = 'mailchimp:sync:all-events';

    private const COMMITTEE_TYPE = 'committee';
    private const TERRITORIAL_COUNCIL_TYPE = 'territorial_council';

    private static $allTypes = [
        self::COMMITTEE_TYPE,
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

        $paginator = $this->buildPaginator($type);

        $count = $paginator->count();
        $total = $limit && $limit < $count ? $limit : $count;

        if (false === $this->io->confirm(sprintf('Are you sure to sync %d events?', $total), false)) {
            return 1;
        }

        $paginator->getQuery()->setMaxResults($limit && $limit < 500 ? $limit : 500);

        $this->io->progressStart($total);
        $offset = 0;

        do {
            foreach ($paginator->getIterator() as $object) {
                $this->bus->dispatch(new CreateStaticSegmentCommand($object->getUuid(), \get_class($object)));

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

        return 0;
    }

    private function buildPaginator(string $type): Paginator
    {
        return new Paginator($this->getQueryBuilder($type)->getQuery());
    }

    private function getQueryBuilder(string $type): QueryBuilder
    {
        switch ($type) {
            case self::COMMITTEE_TYPE:
                return $this->entityManager
                    ->getRepository(Committee::class)
                    ->createQueryBuilder('committee')
                    ->select('PARTIAL committee.{id, uuid}')
                    ->innerJoin(CommitteeMembership::class, 'membership', Join::WITH, 'membership.committee = committee')
                    ->groupBy('committee')
                ;

            case self::TERRITORIAL_COUNCIL_TYPE:
                return $this->entityManager
                    ->getRepository(TerritorialCouncil::class)
                    ->createQueryBuilder('council')
                    ->select('PARTIAL council.{id, uuid}')
                ;
        }

        throw new \InvalidArgumentException('Invalid message type');
    }
}
