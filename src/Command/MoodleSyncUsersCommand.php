<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Moodle\User;
use App\Formation\Moodle\UserManager;
use App\Repository\Moodle\MoodleUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:moodle:sync-users',
    description: 'Backfill Moodle profile fields (name, country, city, department) and jobs for every adherent linked to a Moodle account',
)]
class MoodleSyncUsersCommand extends Command
{
    private const int DRY_RUN_DETAIL_LIMIT = 10;

    public function __construct(
        private readonly MoodleUserRepository $moodleUserRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserManager $userManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('adherentIds', InputArgument::IS_ARRAY, 'Adherent ids (int) to sync; syncs all linked Moodle users when omitted')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show the fields that would be sent without calling Moodle');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');
        $ids = array_values(array_filter(array_map('intval', $input->getArgument('adherentIds')), static fn (int $id) => $id > 0));

        $moodleUsers = $ids
            ? $this->moodleUserRepository->findByAdherentIds($ids)
            : $this->moodleUserRepository->findAll();

        if ($ids && ($missing = array_diff($ids, array_map(static fn (User $moodleUser) => $moodleUser->adherent->getId(), $moodleUsers)))) {
            $io->warning(\sprintf('No Moodle account for adherent id(s): %s', implode(', ', $missing)));
        }

        if (0 === $total = \count($moodleUsers)) {
            $io->success('No Moodle users to sync.');

            return self::SUCCESS;
        }

        if ($dryRun) {
            return $this->dryRun($io, $moodleUsers, $total);
        }

        $adherentUuids = array_map(static fn (User $moodleUser) => $moodleUser->adherent->getUuid()->toRfc4122(), $moodleUsers);

        $io->info(\sprintf('Syncing %d Moodle users.', $total));
        $io->progressStart($total);

        $failures = [];

        foreach ($adherentUuids as $uuid) {
            try {
                $this->userManager->updateUser($uuid);
            } catch (\Throwable $e) {
                $failures[] = \sprintf('%s: %s', $uuid, $e->getMessage());
            }

            $io->progressAdvance();
            $this->entityManager->clear();
        }

        $io->progressFinish();

        if ($failures) {
            $io->warning(\sprintf('Sync complete: %d succeeded, %d failed.', $total - \count($failures), \count($failures)));
            $io->listing($failures);

            return self::FAILURE;
        }

        $io->success(\sprintf('Sync complete: %d users.', $total));

        return self::SUCCESS;
    }

    /**
     * @param User[] $moodleUsers
     */
    private function dryRun(SymfonyStyle $io, array $moodleUsers, int $total): int
    {
        $io->info(\sprintf('[dry-run] %d Moodle user(s) would be synced. No request is sent to Moodle.', $total));

        if ($total >= self::DRY_RUN_DETAIL_LIMIT) {
            $io->note(\sprintf('Per-user detail is only shown below %d users. Pass explicit adherent ids to preview the payloads.', self::DRY_RUN_DETAIL_LIMIT));

            return self::SUCCESS;
        }

        $rows = [];

        foreach ($moodleUsers as $moodleUser) {
            $adherent = $moodleUser->adherent;
            $fields = $this->userManager->getSyncedFields($adherent);
            $rows[] = [
                $adherent->getId(),
                $adherent->getEmailAddress(),
                implode("\n", array_map(static fn (string $key, string $value) => \sprintf('%s = %s', $key, $value), array_keys($fields), $fields)),
            ];
        }

        $io->table(['Adherent id', 'Email', 'Target fields'], $rows);

        return self::SUCCESS;
    }
}
