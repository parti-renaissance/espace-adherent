<?php

declare(strict_types=1);

namespace App\Command;

use App\Adherent\Contribution\ContributionAmountUtils;
use App\Adherent\Contribution\ContributionRequestHandler;
use App\Entity\Adherent;
use App\Entity\Contribution\Contribution;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:contribution:sync-stale-subscriptions',
    description: 'Detects and fixes adherents whose GoCardless subscription is out of sync with their latest revenue declaration.',
)]
class ContributionSyncStaleSubscriptionsCommand extends Command
{
    public function __construct(
        private readonly ContributionRequestHandler $contributionRequestHandler,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NEGATABLE, 'Run without applying changes', true)
            ->addOption('adherent-uuid', null, InputOption::VALUE_REQUIRED, 'Process only the adherent with this UUID')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');
        $adherentUuid = $input->getOption('adherent-uuid');
        $adherentUuid = \is_string($adherentUuid) ? $adherentUuid : null;

        if ($dryRun) {
            $io->warning('DRY RUN mode — no changes will be applied.');
        }

        $processed = 0;
        $corrected = 0;
        $errors = 0;

        foreach ($this->findStaleAdherents($adherentUuid) as $adherent) {
            ++$processed;

            $lastDeclaration = $adherent->getLastRevenueDeclaration();
            if (null === $lastDeclaration) {
                continue;
            }

            $newExpectedAmount = ContributionAmountUtils::getContributionAmount($lastDeclaration->amount);
            $needContribution = ContributionAmountUtils::needContribution($lastDeclaration->amount);

            $previousDeclarations = $adherent->getRevenueDeclarations()->slice(1, 1);
            $currentAmount = $previousDeclarations
                ? ContributionAmountUtils::getContributionAmount(reset($previousDeclarations)->amount)
                : null;

            $action = !$needContribution
                ? 'CANCEL'
                : \sprintf('UPDATE %s → %d€/month', null !== $currentAmount ? $currentAmount.'€/month' : '?', $newExpectedAmount);

            $io->writeln(\sprintf(
                '- %s %s (uuid=%s): currently paying %s, should pay %d€/month → %s',
                $adherent->getFirstName(),
                $adherent->getLastName(),
                $adherent->getUuidAsString(),
                null !== $currentAmount ? $currentAmount.'€/month' : '?',
                $newExpectedAmount,
                $action,
            ));

            if ($dryRun) {
                continue;
            }

            try {
                if (!$needContribution) {
                    $this->contributionRequestHandler->cancelLastContribution($adherent);
                    $adherent->setContributedAt(new \DateTime());
                    $this->entityManager->flush();
                } else {
                    $this->contributionRequestHandler->handleAmountChange($adherent, $newExpectedAmount);
                }
                ++$corrected;
            } catch (\Throwable $exception) {
                ++$errors;
                $this->logger->error('Failed to sync stale contribution', [
                    'adherent_uuid' => $adherent->getUuidAsString(),
                    'exception' => $exception,
                ]);
                $io->error(\sprintf('Failed on adherent %s: %s', $adherent->getUuidAsString(), $exception->getMessage()));
            }
        }

        $io->success(\sprintf(
            'Processed %d adherent(s). Corrected: %d. Errors: %d.%s',
            $processed,
            $corrected,
            $errors,
            $dryRun ? ' (dry-run, nothing persisted)' : '',
        ));

        return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * @return list<Adherent>
     */
    private function findStaleAdherents(?string $adherentUuid): array
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('DISTINCT a')
            ->from(Adherent::class, 'a')
            ->innerJoin('a.revenueDeclarations', 'rd')
            ->innerJoin('a.contributions', 'c')
            ->where('rd.createdAt = (SELECT MAX(rd2.createdAt) FROM App\Entity\Contribution\RevenueDeclaration rd2 WHERE rd2.adherent = a)')
            ->andWhere('c.createdAt = (SELECT MAX(c2.createdAt) FROM App\Entity\Contribution\Contribution c2 WHERE c2.adherent = a)')
            ->andWhere('rd.createdAt > c.createdAt')
            ->andWhere('c.gocardlessSubscriptionStatus NOT IN (:cancelledStatuses)')
            ->setParameter('cancelledStatuses', Contribution::INACTIVE_SUBSCRIPTION_STATUSES)
            ->orderBy('rd.createdAt', 'ASC')
        ;

        if (null !== $adherentUuid) {
            $qb->andWhere('a.uuid = :uuid')->setParameter('uuid', $adherentUuid);
        }

        return $qb->getQuery()->getResult();
    }
}
