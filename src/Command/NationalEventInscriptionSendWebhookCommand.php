<?php

declare(strict_types=1);

namespace App\Command;

use App\NationalEvent\Command\SendWebhookCommand;
use App\Repository\NationalEvent\EventInscriptionRepository;
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
use Symfony\Contracts\Service\Attribute\Required;

#[AsCommand(
    name: 'app:national-event:inscription:send-webhook',
    description: 'Send webhook for each national event inscription'
)]
class NationalEventInscriptionSendWebhookCommand extends Command
{
    private SymfonyStyle $io;
    private EventInscriptionRepository $eventInscriptionRepository;
    private MessageBusInterface $bus;

    protected function configure(): void
    {
        $this
            ->addArgument('event-id', InputArgument::REQUIRED)
            ->addOption('emails', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $paginator = $this->getQueryBuilder($input->getArgument('event-id'), $input->getOption('emails'));

        $count = $paginator->count();

        if (!$count || false === $this->io->confirm(\sprintf('Are you sure to sync %d inscriptions ?', $count), false)) {
            return self::FAILURE;
        }

        $paginator->getQuery()->setMaxResults(500);

        $this->io->progressStart($count);
        $offset = 0;

        do {
            foreach ($paginator as $eventInscription) {
                $this->bus->dispatch(new SendWebhookCommand($eventInscription->getUuid()));

                $this->io->progressAdvance();
                ++$offset;
            }

            $paginator->getQuery()->setFirstResult($offset);
        } while ($offset < $count);

        $this->io->progressFinish();

        return self::SUCCESS;
    }

    private function getQueryBuilder(int $eventId, array $emails): Paginator
    {
        $queryBuilder = $this->eventInscriptionRepository
            ->createQueryBuilder('event_inscription')
            ->select('PARTIAL event_inscription.{id, uuid}')
            ->innerJoin('event_inscription.event', 'event')
            ->where('event.id = :id')
            ->setParameter('id', $eventId)
        ;

        if ($emails) {
            $queryBuilder
                ->andWhere('event_inscription.addressEmail IN (:emails)')
                ->setParameter('emails', $emails)
            ;
        }

        return new Paginator($queryBuilder->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true));
    }

    #[Required]
    public function setEventInscriptionRepository(EventInscriptionRepository $eventInscriptionRepository): void
    {
        $this->eventInscriptionRepository = $eventInscriptionRepository;
    }

    #[Required]
    public function setBus(MessageBusInterface $bus): void
    {
        $this->bus = $bus;
    }
}
