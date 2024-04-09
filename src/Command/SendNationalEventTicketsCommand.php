<?php

namespace App\Command;

use App\Entity\NationalEvent\EventInscription;
use App\Mailer\MailerService;
use App\Mailer\Message\BesoinDEurope\NationalEventTicketMessage;
use App\NationalEvent\InscriptionStatusEnum;
use App\Repository\NationalEvent\EventInscriptionRepository;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Endroid\QrCode\Builder\BuilderInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:national-event:send-tickets')]
class SendNationalEventTicketsCommand extends Command
{
    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        private readonly EventInscriptionRepository $eventInscriptionRepository,
        private readonly ObjectManager $entityManager,
        private readonly MailerService $transactionalMailer,
        private readonly BuilderInterface $builder,
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
        $paginator = $this->getQueryBuilder($emails = $input->getOption('email'));

        if (0 === $total = $paginator->count()) {
            $this->io->success('No tickets to send.');

            return self::SUCCESS;
        }

        if (!$this->io->isQuiet() && false === $this->io->confirm(sprintf('Are you sure to send %d tickets ?', $total), false)) {
            return self::FAILURE;
        }

        $this->io->progressStart($total);

        do {
            foreach ($paginator as $eventInscription) {
                $this->transactionalMailer->sendMessage(NationalEventTicketMessage::create(
                    $eventInscription,
                    $this->builder->data($eventInscription->getUuid()->toString())->build()->getDataUri()
                ));

                $eventInscription->ticketSentAt = new \DateTime();

                $this->entityManager->flush();

                $this->io->progressAdvance();
            }

            $this->entityManager->clear();
        } while (empty($emails) && iterator_count($paginator->getIterator()));

        $this->io->progressFinish();

        return self::SUCCESS;
    }

    /**
     * @return Paginator|EventInscription[]
     */
    private function getQueryBuilder(array $emails): Paginator
    {
        $queryBuilder = $this->eventInscriptionRepository
            ->createQueryBuilder('event_inscription')
            ->innerJoin('event_inscription.event', 'event')
            ->addSelect('event')
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
                ->setParameter('status', [InscriptionStatusEnum::ACCEPTED, InscriptionStatusEnum::INCONCLUSIVE])
                ->setMaxResults(500)
            ;
        }

        return new Paginator($queryBuilder->getQuery());
    }
}
