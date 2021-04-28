<?php

namespace App\Command;

use App\Mailchimp\Synchronisation\Command\CoalitionContactChangeCommand;
use App\Repository\AdherentRepository;
use App\Repository\Coalition\CauseFollowerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class MailchimpSyncCoalitionsContactsCommand extends Command
{
    protected static $defaultName = 'mailchimp:sync:all-coalitions-contacts';

    private $adherentRepository;
    private $causeFollowerRepository;
    private $entityManager;
    private $bus;
    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        AdherentRepository $adherentRepository,
        CauseFollowerRepository $causeFollowerRepository,
        EntityManagerInterface $entityManager,
        MessageBusInterface $bus
    ) {
        $this->adherentRepository = $adherentRepository;
        $this->causeFollowerRepository = $causeFollowerRepository;
        $this->entityManager = $entityManager;
        $this->bus = $bus;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_REQUIRED)
            ->addOption('adherents-only', null, InputOption::VALUE_NONE)
            ->addOption('subscribed-adherents-only', null, InputOption::VALUE_NONE)
            ->addOption('followers-only', null, InputOption::VALUE_NONE)
        ;
    }

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $limit = (int) $input->getOption('limit');
        $onlyFollowers = $input->getOption('followers-only');
        $onlyAdherents = $input->getOption('adherents-only');
        $onlySubscribedAdherents = $input->getOption('subscribed-adherents-only');

        // Adherents
        if (!$onlyFollowers) {
            $paginator = $this->getAdherentsQueryBuilder($onlySubscribedAdherents);

            $count = $paginator->count();
            $total = $limit && $limit < $count ? $limit : $count;

            if (false === $this->io->confirm(sprintf('Are you sure to sync %d adherents from "Colitions" ?', $total), false)) {
                return 1;
            }

            $this->syncContacts(true, $paginator, $count, $total, $limit);
        }

        // Cause followers
        if (!$onlyAdherents) {
            $paginator = $this->getCauseFollowersQueryBuilder();

            $count = $paginator->count();
            $total = $limit && $limit < $count ? $limit : $count;

            if (false === $this->io->confirm(sprintf('Are you sure to sync %d cause followers from "Colitions" ?', $total), false)) {
                return 1;
            }

            $this->syncContacts(false, $paginator, $count, $total, $limit);
        }
    }

    private function syncContacts(bool $isAdherent, Paginator $paginator, int $count, int $total, int $limit): void
    {
        $paginator->getQuery()->setMaxResults($limit && $limit < 2 ? $limit : 2);

        $this->io->progressStart($total);
        $offset = 0;

        do {
            foreach ($paginator->getIterator() as $contact) {
                $this->bus->dispatch(new CoalitionContactChangeCommand($contact->getEmailAddress(), $isAdherent));

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

    private function getAdherentsQueryBuilder(bool $onlySubscribedAdherents): Paginator
    {
        if ($onlySubscribedAdherents) {
            $queryBuilder = $this
                ->adherentRepository
                ->createCoalitionSubscribersQueryBuilder()
            ;
        } else {
            $queryBuilder = $this->adherentRepository->createQueryBuilder('adherent');
        }

        return new Paginator($queryBuilder->getQuery());
    }

    private function getCauseFollowersQueryBuilder(): Paginator
    {
        $queryBuilder = $this
            ->causeFollowerRepository
            ->createSubscribedQueryBuilder()
        ;

        return new Paginator($queryBuilder->getQuery());
    }
}
