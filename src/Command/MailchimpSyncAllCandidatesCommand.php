<?php

namespace App\Command;

use App\Entity\ApplicationRequest\RunningMateRequest;
use App\Entity\ApplicationRequest\VolunteerRequest;
use App\Mailchimp\Synchronisation\Command\AddApplicationRequestCandidateCommand;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'mailchimp:sync:all-candidates',
    description: 'Send all municipal candidates to Mailchimp',
)]
class MailchimpSyncAllCandidatesCommand extends Command
{
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

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $volunteerPaginator = $this->getVolunteerPaginator();
        $runningMatePaginator = $this->getRunningMatePaginator();

        $volunteerCount = $volunteerPaginator->count();
        $runningMateCount = $runningMatePaginator->count();

        if (false === $this->io->confirm(\sprintf('Are you sure to sync %d volunteer and %d running mate candidates?', $volunteerCount, $runningMateCount), false)) {
            return self::FAILURE;
        }

        $this->io->progressStart($volunteerCount + $runningMateCount);

        $this->doSync($volunteerPaginator, $volunteerCount);
        $this->doSync($runningMatePaginator, $runningMateCount);

        $this->io->progressFinish();

        return self::SUCCESS;
    }

    private function getVolunteerPaginator(): Paginator
    {
        return new Paginator($this->entityManager
            ->getRepository(VolunteerRequest::class)
            ->createQueryBuilder('candidate')
            ->where('candidate.displayed = true')
            ->orderBy('candidate.createdAt', 'ASC')
            ->setMaxResults(500)
        );
    }

    private function getRunningMatePaginator(): Paginator
    {
        return new Paginator($this->entityManager
            ->getRepository(RunningMateRequest::class)
            ->createQueryBuilder('candidate')
            ->where('candidate.displayed = true')
            ->orderBy('candidate.createdAt', 'ASC')
            ->setMaxResults(500)
        );
    }

    private function doSync(Paginator $paginator, int $count): void
    {
        $offset = 0;

        do {
            foreach ($paginator->getIterator() as $candidate) {
                $this->bus->dispatch(new AddApplicationRequestCandidateCommand(
                    $candidate->getId(),
                    $candidate::class
                ));

                $this->io->progressAdvance();

                ++$offset;
            }

            $paginator->getQuery()->setFirstResult($offset);

            $this->entityManager->clear();
        } while (0 !== $offset && $offset < $count);
    }
}
