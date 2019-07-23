<?php

namespace AppBundle\Command;

use AppBundle\Entity\ApplicationRequest\RunningMateRequest;
use AppBundle\Entity\ApplicationRequest\VolunteerRequest;
use AppBundle\Mailchimp\Synchronisation\Command\AddApplicationRequestCandidateCommand;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class MailchimpSyncAllCandidatesCommand extends Command
{
    protected static $defaultName = 'mailchimp:sync:all-candidates';

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

    protected function configure()
    {
        $this->setDescription('Send all municipal candidates to Mailchimp');
    }

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $volunteerPaginator = $this->getVolunteerPaginator();
        $runningMatePaginator = $this->getRunningMatePaginator();

        $volunteerCount = $volunteerPaginator->count();
        $runningMateCount = $runningMatePaginator->count();

        if (false === $this->io->confirm(sprintf('Are you sure to sync %d volunteer and %d running mate candidates?', $volunteerCount, $runningMateCount), false)) {
            return 1;
        }

        $this->io->progressStart($volunteerCount + $runningMateCount);

        $this->doSync($volunteerPaginator, $volunteerCount);
        $this->doSync($runningMatePaginator, $runningMateCount);

        $this->io->progressFinish();
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
                    \get_class($candidate)
                ));

                $this->io->progressAdvance();

                ++$offset;
            }

            $paginator->getQuery()->setFirstResult($offset);

            $this->entityManager->clear();
        } while (0 !== $offset && $offset < $count);
    }
}
