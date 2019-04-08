<?php

namespace AppBundle\Command;

use AppBundle\Entity\CommitteeMembership;
use AppBundle\Mailchimp\Synchronisation\Command\AddAdherentToCommitteeStaticSegmentCommand;
use AppBundle\Repository\CommitteeMembershipRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class MailchimpSyncAllCommitteeMembershipsCommand extends Command
{
    protected static $defaultName = 'mailchimp:sync:all-committee-memberships';

    private $repository;
    private $entityManager;
    private $bus;
    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        CommitteeMembershipRepository $repository,
        ObjectManager $entityManager,
        MessageBusInterface $bus
    ) {
        $this->repository = $repository;
        $this->entityManager = $entityManager;
        $this->bus = $bus;

        parent::__construct();
    }

    protected function configure()
    {
        $this->addOption('limit', null, InputOption::VALUE_REQUIRED, null);
    }

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $limit = (int) $input->getOption('limit');

        $paginator = $this->getQueryBuilder();

        $count = $paginator->count();
        $total = $limit && $limit < $count ? $limit : $count;

        if (false === $this->io->confirm(sprintf('Are you sure to sync %d adherents?', $total), false)) {
            return 1;
        }

        $paginator->getQuery()->setMaxResults($limit && $limit < 500 ? $limit : 500);

        $this->io->progressStart($total);
        $offset = 0;

        do {
            foreach ($paginator->getIterator() as $membership) {
                /** @var CommitteeMembership $membership */
                $this->bus->dispatch(new AddAdherentToCommitteeStaticSegmentCommand(
                    $membership->getAdherent()->getUuid(),
                    $membership->getCommittee()->getUuid()
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

    private function getQueryBuilder(): Paginator
    {
        $queryBuilder = $this->repository
            ->createQueryBuilder('membership')
            ->addSelect('PARTIAL adherent.{id, uuid}, PARTIAL committee.{id, uuid}')
            ->innerJoin('membership.adherent', 'adherent')
            ->innerJoin('membership.committee', 'committee')
        ;

        return new Paginator($queryBuilder->getQuery());
    }
}
