<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Moodle\User;
use App\Formation\Moodle\Driver;
use App\Formation\Moodle\UserManager;
use App\Repository\Moodle\MoodleUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:moodle:resync-adherent-jobs',
    description: 'Resync adherent jobs in Moodle with correct startdate from firstMembershipDonation',
)]
class MoodleResyncAdherentJobsCommand extends Command
{
    public function __construct(
        private readonly MoodleUserRepository $moodleUserRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly Driver $driver,
        private readonly UserManager $userManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $moodleUsers = $this->moodleUserRepository->findAll();

        if (0 === \count($moodleUsers)) {
            $io->success('No Moodle users found.');

            return self::SUCCESS;
        }

        $io->info(\sprintf('Found %d Moodle users.', \count($moodleUsers)));

        $removedCount = 0;

        /** @var User $moodleUser */
        foreach ($moodleUsers as $moodleUser) {
            foreach ($moodleUser->getJobs() as $job) {
                if (!str_contains($job->jobKey, '-adherent-')) {
                    continue;
                }

                try {
                    $this->driver->removeJob($job->moodleId);
                } catch (\Throwable $e) {
                    $io->warning(\sprintf('Failed to remove Moodle job %d: %s', $job->moodleId, $e->getMessage()));
                }

                $moodleUser->removeJob($job);
                ++$removedCount;
            }

            $this->entityManager->flush();
        }

        $io->info(\sprintf('Removed %d adherent jobs.', $removedCount));

        $this->entityManager->clear();

        $moodleUsers = $this->moodleUserRepository->findAll();
        $io->progressStart(\count($moodleUsers));

        foreach ($moodleUsers as $moodleUser) {
            $this->userManager->updateUser($moodleUser->adherent->getUuid()->toRfc4122());
            $io->progressAdvance();

            $this->entityManager->clear();
        }

        $io->progressFinish();
        $io->success('Resync complete.');

        return self::SUCCESS;
    }
}
