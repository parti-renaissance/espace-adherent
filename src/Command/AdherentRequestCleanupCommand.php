<?php

namespace App\Command;

use App\Entity\Renaissance\Adhesion\AdherentRequest;
use App\Repository\Renaissance\Adhesion\AdherentRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:adherent-request:cleanup',
    description: 'Cleanup expired adherent requests after a given number of days.',
)]
class AdherentRequestCleanupCommand extends Command
{
    private const FLUSH_BATCH_SIZE = 100;

    private SymfonyStyle $io;

    public function __construct(
        private readonly AdherentRequestRepository $adherentRequestRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'days',
                InputArgument::REQUIRED,
                'Cleanup adherent requests created more than this number of days ago'
            )
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $days = (int) $input->getArgument('days');
        $from = (new \DateTime())->modify(\sprintf('-%d days', $days));

        $this->io->title(\sprintf(
            'Cleaning up adherent requests created before %s',
            $from->format('Y-m-d')
        ));

        /** @var AdherentRequest[] $adherentRequests */
        $adherentRequests = $this->adherentRequestRepository
            ->createQueryBuilder('ar')
            ->where('ar.createdAt <= :from')
            ->andWhere('ar.cleaned = false')
            ->setParameter('from', $from)
            ->getQuery()
            ->toIterable()
        ;

        $count = 0;
        foreach ($adherentRequests as $adherentRequest) {
            $adherentRequest->clean();
            ++$count;

            if (0 === $count % self::FLUSH_BATCH_SIZE) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->io->success(\sprintf('%d adherent requests cleaned.', $count));

        return Command::SUCCESS;
    }
}
