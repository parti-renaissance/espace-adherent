<?php

namespace App\Command;

use App\Adherent\Campus\AdherentRegistrationCommand;
use App\Entity\Adherent;
use App\Membership\MembershipSourceEnum;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:campus:send-webhook',
)]
class SendCampusWebhookCommand extends Command
{
    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly ObjectManager $entityManager,
        private readonly MessageBusInterface $bus
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('from-date', null, InputOption::VALUE_REQUIRED)
            ->addOption('emails', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $paginator = $this->getQueryBuilder(
            new \DateTime($input->getOption('from-date')),
            $input->getOption('emails')
        );

        $total = $paginator->count();

        if (false === $this->io->confirm(sprintf('Are you sure to sync %d adherents?', $total), false)) {
            return self::FAILURE;
        }

        $paginator->getQuery()->setMaxResults(500);

        $this->io->progressStart($total);
        $offset = 0;

        do {
            foreach ($paginator as $adherent) {
                $this->bus->dispatch(new AdherentRegistrationCommand($adherent->getUuid()));

                sleep(1);
                $this->io->progressAdvance();
                ++$offset;
            }

            $paginator->getQuery()->setFirstResult($offset);

            $this->entityManager->clear();
        } while ($offset < $total);

        $this->io->progressFinish();

        return self::SUCCESS;
    }

    /**
     * @return Paginator|Adherent[]
     */
    private function getQueryBuilder(\DateTime $from, array $emails): Paginator
    {
        $queryBuilder = $this->adherentRepository
            ->createQueryBuilder('adherent')
            ->where('adherent.status = :status')
            ->andWhere('adherent.source = :source')
            ->andWhere('adherent.lastMembershipDonation >= :date')
            ->setParameters([
                'status' => Adherent::ENABLED,
                'source' => MembershipSourceEnum::RENAISSANCE,
                'date' => $from,
            ])
        ;

        if ($emails) {
            $queryBuilder
                ->andWhere('adherent.emailAddress IN (:emails)')
                ->setParameter('emails', $emails)
            ;
        }

        return new Paginator($queryBuilder->getQuery());
    }
}
