<?php

namespace App\Command;

use App\Entity\NationalEvent\EventInscription;
use App\NationalEvent\Command\SendTicketCommand;
use App\NationalEvent\InscriptionStatusEnum;
use App\Repository\NationalEvent\EventInscriptionRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand('app:national-event:send-tickets')]
class SendNationalEventTicketsCommand extends Command
{
    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        private readonly EventInscriptionRepository $eventInscriptionRepository,
        private readonly MessageBusInterface $messageBus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('email', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY);
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $inscriptions = $this->getQueryBuilder($input->getOption('email'));

        if (0 === $total = \count($inscriptions)) {
            $this->io->success('No tickets to send.');

            return self::SUCCESS;
        }

        if (!$this->io->isQuiet() && false === $this->io->confirm(\sprintf('Are you sure to send %d tickets ?', $total), false)) {
            return self::FAILURE;
        }

        $this->io->progressStart($total);

        foreach ($inscriptions as $eventInscription) {
            $this->messageBus->dispatch(new SendTicketCommand($eventInscription->getUuid()));

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        return self::SUCCESS;
    }

    /**
     * @return EventInscription[]
     */
    private function getQueryBuilder(array $emails): array
    {
        $queryBuilder = $this->eventInscriptionRepository
            ->createQueryBuilder('event_inscription')
            ->select('PARTIAL event_inscription.{id, uuid}')
        ;

        if ($emails) {
            $queryBuilder
                ->andWhere('event_inscription.addressEmail IN (:emails)')
                ->setParameter('emails', $emails)
            ;
        } else {
            $queryBuilder
                ->andWhere('event_inscription.status IN (:status)')
                ->andWhere('event_inscription.ticketSentAt IS NULL')
                ->setParameter('status', InscriptionStatusEnum::APPROVED_STATUSES)
            ;
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
