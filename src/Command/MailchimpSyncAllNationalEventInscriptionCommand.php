<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\NationalEvent\EventInscription;
use App\Mailchimp\Synchronisation\Command\NationalEventInscriptionChangeCommand;
use App\NationalEvent\InscriptionStatusEnum;
use App\Repository\NationalEvent\EventInscriptionRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
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
        private readonly EventInscriptionRepository $eventInscriptionRepository,
        private readonly MessageBusInterface $bus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('event-id', null, InputOption::VALUE_REQUIRED)
            ->addOption('emails', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $paginator = $this->getQueryBuilder($input->getOption('emails'), $input->getOption('event-id'));

        $count = $paginator->count();

        if (0 === $count) {
            $this->io->warning('No event inscription to sync');

            return self::SUCCESS;
        }

        if (false === $this->io->confirm(\sprintf('Are you sure to sync %d event inscriptions?', $count), false)) {
            return self::FAILURE;
        }

        $this->io->progressStart($count);

        foreach ($paginator as $i => $eventInscription) {
            $this->bus->dispatch(new NationalEventInscriptionChangeCommand($eventInscription->getUuid()));

            $this->io->progressAdvance();

            if (0 === $i % 2) {
                usleep(500);
            }
        }

        $this->io->progressFinish();

        return self::SUCCESS;
    }

    /**
     * @return Paginator|EventInscription[]
     */
    private function getQueryBuilder(array $emails, ?int $eventId): Paginator
    {
        $queryBuilder = $this->eventInscriptionRepository
            ->createQueryBuilder('ei')
            ->select('PARTIAL ei.{id, uuid, addressEmail}')
            ->addSelect(
                'CASE WHEN ei.status IN (:first_statuses) THEN 1
                WHEN ei.status = :waiting_payment THEN 6
                WHEN ei.status IN (:pending_statuses) THEN 9
                WHEN ei.status IN (:approved_statuses) THEN 10
                ELSE 3 END AS HIDDEN score'
            )
            ->innerJoin('ei.event', 'e')
            ->andWhere('e.mailchimpSync = :true')
            ->andWhere('ei.status != :cancelled')
            ->setParameter('true', true)
            ->setParameter('waiting_payment', InscriptionStatusEnum::WAITING_PAYMENT)
            ->setParameter('first_statuses', [
                InscriptionStatusEnum::DUPLICATE,
                InscriptionStatusEnum::REFUSED,
            ])
            ->setParameter('pending_statuses', [
                InscriptionStatusEnum::PENDING,
                InscriptionStatusEnum::IN_VALIDATION,
            ])
            ->setParameter('approved_statuses', InscriptionStatusEnum::APPROVED_STATUSES)
            ->setParameter('cancelled', InscriptionStatusEnum::CANCELED)
            ->orderBy('score', 'ASC')
        ;

        if ($eventId) {
            $queryBuilder
                ->andWhere('e.id = :eventId')
                ->setParameter('eventId', $eventId)
            ;
        }

        if ($emails) {
            $queryBuilder
                ->andWhere('ei.addressEmail IN (:emails)')
                ->setParameter('emails', $emails)
            ;
        }

        return new Paginator($queryBuilder->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true));
    }
}
