<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Adherent;
use App\Entity\Moodle\User;
use App\Formation\Moodle\Driver;
use App\Formation\Moodle\Repair\DuplicateAccountResolver;
use App\Formation\Moodle\Repair\RepairPlan;
use App\Formation\Moodle\Repair\RepairStatus;
use App\Repository\Moodle\MoodleUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:moodle:repair-duplicate-users',
    description: 'Repair adherents whose email change created a duplicate (empty) Moodle account, restoring their progress.',
)]
class MoodleRepairDuplicateUsersCommand extends Command
{
    public function __construct(
        private readonly MoodleUserRepository $moodleUserRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly Driver $driver,
        private readonly DuplicateAccountResolver $resolver,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('force', null, InputOption::VALUE_NONE, 'Apply the repair on Moodle and in the database (default: dry-run).')
            ->addOption('adherent', null, InputOption::VALUE_REQUIRED, 'Restrict the run to a single adherent UUID.')
            ->addOption('adherent-id', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Restrict the run to these adherent database IDs (repeatable and/or comma-separated).')
            ->addOption('uuids-file', null, InputOption::VALUE_REQUIRED, 'Restrict the run to the adherent UUIDs listed in this file (one per line).')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $force = (bool) $input->getOption('force');
        $targetUuids = $this->resolveTargetUuids($input);
        $targetIds = $this->resolveTargetIds($input);
        $hasTarget = $targetUuids || $targetIds;

        if (!$force) {
            $io->warning('DRY-RUN: no change will be made. Re-run with --force to apply.');
        }

        if ($hasTarget) {
            $io->note(\sprintf('Restricted to %d adherent(s).', \count($targetUuids) + \count($targetIds)));
        }

        $counters = ['healthy' => 0, 'repair' => 0, 'manual' => 0, 'applied' => 0, 'errors' => 0];

        foreach ($this->moodleUserRepository->findAll() as $moodleUser) {
            $adherent = $moodleUser->adherent;

            if (
                $hasTarget
                && !isset($targetUuids[$adherent->getUuidAsString()])
                && !isset($targetIds[$adherent->getId()])
            ) {
                continue;
            }

            $currentEmail = $adherent->getEmailAddress();

            try {
                $accounts = $this->collectAccounts($adherent, $moodleUser->moodleId);
            } catch (\Throwable $exception) {
                ++$counters['errors'];
                $io->error(\sprintf('Moodle lookup failed for adherent %s: %s', $adherent->getUuidAsString(), $exception->getMessage()));

                continue;
            }

            $plan = $this->resolver->resolve($currentEmail, $accounts);

            if (RepairStatus::HEALTHY === $plan->status) {
                ++$counters['healthy'];

                continue;
            }

            $io->section(\sprintf('Adherent %s <%s> (linked moodleId #%d)', $adherent->getUuidAsString(), $currentEmail, $moodleUser->moodleId));
            $this->renderAccounts($io, $accounts);
            $io->writeln($plan->reason);

            if (RepairStatus::MANUAL === $plan->status) {
                ++$counters['manual'];
                $io->warning('Skipped — manual review required.');

                continue;
            }

            ++$counters['repair'];

            if (!$force) {
                continue;
            }

            try {
                $this->apply($moodleUser, $plan);
                ++$counters['applied'];
                $io->success(\sprintf('Repaired: kept #%d, deleted #%d.', $plan->keepMoodleId, $plan->deleteMoodleId));
            } catch (\Throwable $exception) {
                ++$counters['errors'];
                $io->error(\sprintf('Repair failed for adherent %s: %s', $adherent->getUuidAsString(), $exception->getMessage()));
            }
        }

        $io->section('Summary');
        $io->definitionList(
            ['Healthy' => $counters['healthy']],
            ['Repairable' => $counters['repair']],
            ['Applied' => $counters['applied']],
            ['Manual review' => $counters['manual']],
            ['Errors' => $counters['errors']],
        );

        if (!$force && $counters['repair'] > 0) {
            $io->note('Re-run with --force to apply the repairs above.');
        }

        return $counters['errors'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Builds the set of adherent UUIDs to restrict the run to (empty = no restriction).
     *
     * @return array<string, true>
     */
    private function resolveTargetUuids(InputInterface $input): array
    {
        $uuids = [];

        if ($single = $input->getOption('adherent')) {
            $uuids[] = (string) $single;
        }

        if ($file = $input->getOption('uuids-file')) {
            if (!is_file($file) || false === $lines = file($file, \FILE_IGNORE_NEW_LINES | \FILE_SKIP_EMPTY_LINES)) {
                throw new \InvalidArgumentException(\sprintf('Cannot read UUIDs file "%s".', $file));
            }

            foreach ($lines as $line) {
                if ('' !== $line = trim($line)) {
                    $uuids[] = $line;
                }
            }
        }

        return array_fill_keys($uuids, true);
    }

    /**
     * Builds the set of adherent database IDs to restrict the run to (empty = no restriction).
     * Accepts repeated flags and/or comma-separated values.
     *
     * @return array<int, true>
     */
    private function resolveTargetIds(InputInterface $input): array
    {
        $ids = [];

        foreach ($input->getOption('adherent-id') as $value) {
            foreach (explode(',', (string) $value) as $part) {
                if ('' !== $part = trim($part)) {
                    $ids[(int) $part] = true;
                }
            }
        }

        return $ids;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function collectAccounts(Adherent $adherent, int $storedMoodleId): array
    {
        $accounts = [];

        if (!empty($stored = $this->driver->findUserById($storedMoodleId))) {
            $accounts[] = $stored;
        }

        foreach ($this->driver->findUsersByName($adherent->getFirstName(), $adherent->getLastName()) as $candidate) {
            $accounts[] = $candidate;
        }

        return $accounts;
    }

    private function apply(User $moodleUser, RepairPlan $plan): void
    {
        // 1. Delete the empty duplicate first to free the email address on Moodle.
        $this->driver->deleteUser($plan->deleteMoodleId);

        // 2. Move the email/username onto the account that holds the progress.
        $this->driver->updateUser($plan->keepMoodleId, [
            'email' => $plan->newEmail,
            'username' => $plan->newEmail,
        ]);

        // 3. Repoint the local link and drop the jobs that lived on the deleted account;
        //    they are re-created on the kept account at the next login.
        $moodleUser->moodleId = $plan->keepMoodleId;

        foreach ($moodleUser->getJobs() as $job) {
            $moodleUser->removeJob($job);
        }

        $this->entityManager->flush();
    }

    /**
     * @param array<int, array<string, mixed>> $accounts
     */
    private function renderAccounts(SymfonyStyle $io, array $accounts): void
    {
        $rows = [];
        foreach ($accounts as $account) {
            $rows[(int) ($account['id'] ?? 0)] = [
                $account['id'] ?? '?',
                $account['username'] ?? '',
                $account['email'] ?? '',
                $this->formatTimestamp($account['timecreated'] ?? null),
                $this->formatTimestamp($account['lastaccess'] ?? null),
            ];
        }

        $io->table(['moodleId', 'username', 'email', 'created', 'lastaccess'], array_values($rows));
    }

    private function formatTimestamp(mixed $value): string
    {
        if (empty($value)) {
            return '-';
        }

        return date('Y-m-d', (int) $value);
    }
}
