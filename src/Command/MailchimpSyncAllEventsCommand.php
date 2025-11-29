<?php

declare(strict_types=1);

namespace App\Command;

use App\AdherentMessage\Command\CreateStaticSegmentCommand;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Doctrine\ORM\Query\Expr\Join;
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
    name: 'mailchimp:sync:all-events',
)]
class MailchimpSyncAllEventsCommand extends Command
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

        $paginator = $this->buildPaginator($type);

        $count = $paginator->count();
        $total = $limit && $limit < $count ? $limit : $count;

        if (false === $this->io->confirm(\sprintf('Are you sure to sync %d events?', $total), false)) {
            return self::FAILURE;
        }

        $paginator->getQuery()->setMaxResults($limit && $limit < 500 ? $limit : 500);

        $this->io->progressStart($total);
        $offset = 0;

        do {
            foreach ($paginator->getIterator() as $object) {
                $this->bus->dispatch(new CreateStaticSegmentCommand($object->getUuid(), $object::class));

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
        }

        throw new \InvalidArgumentException('Invalid message type');
    }
}
