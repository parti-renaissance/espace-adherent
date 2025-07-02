<?php

namespace App\Command;

use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\NationalEvent;
use App\Mailchimp\Synchronisation\Command\NationalEventInscriptionChangeCommand;
use App\Repository\NationalEvent\EventInscriptionRepository;
use App\Repository\NationalEvent\NationalEventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(name: 'mailchimp:sync:all-national-event-inscription')]
class MailchimpSyncAllNationalEventInscriptionCommand extends Command
{
    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        private readonly NationalEventRepository $nationalEventRepository,
        private readonly EventInscriptionRepository $eventInscriptionRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $bus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('event-slug', InputArgument::REQUIRED)
            ->addOption('limit', null, InputOption::VALUE_REQUIRED)
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

        $nationalEvent = $this->nationalEventRepository->findOneBy(['slug' => $input->getArgument('event-slug')]);

        if (!$nationalEvent) {
            $this->io->error(\sprintf('No NationalEvent found with slug "%s".', $input->getArgument('event-slug')));

            return self::FAILURE;
        }

        $paginator = $this->getQueryBuilder(
            $nationalEvent,
            $input->getOption('emails')
        );

        $count = $paginator->count();
        $total = $limit && $limit < $count ? $limit : $count;

        if (false === $this->io->confirm(\sprintf('Are you sure to sync %d event inscriptions?', $total), false)) {
            return self::FAILURE;
        }

        $paginator->getQuery()->setMaxResults($limit && $limit < $batchSize ? $limit : $batchSize);

        $this->io->progressStart($total);
        $offset = 0;

        do {
            foreach ($paginator as $eventInscription) {
                $this->bus->dispatch(new NationalEventInscriptionChangeCommand(
                    $eventInscription->getUuid(),
                    $eventInscription->addressEmail
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
     * @return Paginator|EventInscription[]
     */
    private function getQueryBuilder(
        NationalEvent $nationalEvent,
        array $emails,
    ): Paginator {
        $queryBuilder = $this->eventInscriptionRepository
            ->createQueryBuilder('event_inscription')
            ->select('PARTIAL event_inscription.{id, uuid, addressEmail}')
            ->innerJoin('event_inscription.event', 'event')
            ->andWhere('event.mailchimpSync = :true')
            ->setParameter('true', true)
            ->andWhere('event_inscription.event = :nationalEvent')
            ->setParameter('nationalEvent', $nationalEvent)
        ;

        if ($emails) {
            $queryBuilder
                ->andWhere('event_inscription.addressEmail IN (:emails)')
                ->setParameter('emails', $emails)
            ;
        }

        return new Paginator($queryBuilder->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true));
    }
}
