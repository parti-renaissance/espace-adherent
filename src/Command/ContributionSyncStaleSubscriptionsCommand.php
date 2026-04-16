<?php

declare(strict_types=1);

namespace App\Command;

use App\Adherent\Contribution\ContributionAmountUtils;
use App\Adherent\Contribution\ContributionRequestHandler;
use App\Entity\Adherent;
use App\Entity\Contribution\Contribution;
use App\Entity\Contribution\Payment;
use App\GoCardless\ClientInterface as GoCardlessClientInterface;
use App\Ohme\PaymentStatusEnum;
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
    private const GOCARDLESS_THROTTLE_MICROSECONDS = 500_000;

    public function __construct(
        private readonly ContributionRequestHandler $contributionRequestHandler,
        private readonly EntityManagerInterface $entityManager,
        private readonly GoCardlessClientInterface $gocardless,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NEGATABLE, 'Run without applying changes', true)
            ->addOption('adherent-uuid', null, InputOption::VALUE_REQUIRED, 'Process only the adherent with this UUID')
            ->addOption('exclude-uuid', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Skip these adherent UUIDs (repeatable)')
            ->addOption('interactive', 'i', InputOption::VALUE_NONE, 'Process one adherent at a time, verifying immediately and asking before each next action')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');
        $adherentUuid = $input->getOption('adherent-uuid');
        $adherentUuid = \is_string($adherentUuid) ? $adherentUuid : null;
        $excludeUuids = (array) $input->getOption('exclude-uuid');
        $interactive = (bool) $input->getOption('interactive');

        if ($dryRun) {
            $io->warning('DRY RUN mode — no changes will be applied.');
        }

        if ($interactive && $dryRun) {
            $io->note('Interactive mode has no effect with --dry-run.');
            $interactive = false;
        }

        if ($interactive) {
            $io->note('INTERACTIVE mode — processing a single adherent, verifying, then stopping.');
        }

        if ($excludeUuids) {
            $io->note(\sprintf('Excluding %d adherent(s): %s', \count($excludeUuids), implode(', ', $excludeUuids)));
        }

        $processed = 0;
        $corrected = 0;
        $skipped = 0;
        $errors = 0;

        /** @var list<array{adherent: Adherent, action: string, expected: int, subscriptionId: ?string}> $executed */
        $executed = [];

        $staleAdherents = $this->findStaleAdherents($adherentUuid, $excludeUuids);
        $totalToProcess = \count($staleAdherents);

        foreach ($staleAdherents as $index => $adherent) {
            ++$processed;

            $lastDeclaration = $adherent->getLastRevenueDeclaration();
            if (null === $lastDeclaration) {
                continue;
            }

            $newExpectedAmount = ContributionAmountUtils::getContributionAmount($lastDeclaration->amount);
            $needContribution = ContributionAmountUtils::needContribution($lastDeclaration->amount);

            $declarations = $adherent->getRevenueDeclarations()->slice(0, 2);
            $previousDeclaration = $declarations[1] ?? null;

            $lastConfirmedPayment = $this->findLastConfirmedPayment($adherent);
            $lastContribution = $adherent->getLastContribution();
            $subscriptionId = $lastContribution?->gocardlessSubscriptionId;
            $hasValidSubscriptionId = $this->looksLikeValidSubscriptionId($subscriptionId);
            $gcSubAmount = $hasValidSubscriptionId ? $this->fetchGoCardlessSubscriptionAmount($adherent) : null;

            $noChangeNeeded = $needContribution && null !== $gcSubAmount && $gcSubAmount === $newExpectedAmount;
            $action = match (true) {
                !$hasValidSubscriptionId => 'SKIP (invalid subscription ID)',
                !$needContribution => 'CANCEL',
                $noChangeNeeded => 'SKIP',
                default => 'UPDATE',
            };

            $result = 'DRY-RUN';
            if (!$dryRun) {
                if (!$hasValidSubscriptionId) {
                    $result = 'SKIPPED (invalid subscription ID)';
                    ++$skipped;
                } elseif ($noChangeNeeded) {
                    $result = 'SKIPPED (same amount)';
                    ++$skipped;
                } else {
                    try {
                        if (!$needContribution) {
                            $this->contributionRequestHandler->cancelLastContribution($adherent);
                            $adherent->setContributedAt(new \DateTime());
                            $this->entityManager->flush();
                        } else {
                            $this->contributionRequestHandler->handleAmountChange($adherent, $newExpectedAmount);
                        }
                        $result = 'SUCCESS';
                        ++$corrected;
                        $executed[] = [
                            'adherent' => $adherent,
                            'action' => $action,
                            'expected' => $newExpectedAmount,
                            'subscriptionId' => $subscriptionId,
                        ];
                    } catch (\Throwable $exception) {
                        ++$errors;
                        $result = 'FAILED: '.$exception->getMessage();
                        $this->logger->error('Failed to sync stale contribution', [
                            'adherent_uuid' => $adherent->getUuidAsString(),
                            'exception' => $exception,
                        ]);
                    }
                }
            }

            $io->writeln(\sprintf(
                '[%d/%d] %s %s | uuid=%s | email=%s | last_decl=%d€@%s | prev_decl=%s | contrib=%s@%s(%s) | gc_sub=%s | last_paid=%s | should_pay=%d€/month | action=%s | result=%s',
                $index + 1,
                $totalToProcess,
                $adherent->getFirstName(),
                $adherent->getLastName(),
                $adherent->getUuidAsString(),
                $adherent->getEmailAddress(),
                $lastDeclaration->amount,
                $lastDeclaration->getCreatedAt()->format('Y-m-d'),
                $previousDeclaration
                    ? \sprintf('%d€@%s', $previousDeclaration->amount, $previousDeclaration->getCreatedAt()->format('Y-m-d'))
                    : 'none',
                $subscriptionId ?? 'n/a',
                $lastContribution?->getCreatedAt()?->format('Y-m-d') ?? 'n/a',
                $lastContribution?->gocardlessSubscriptionStatus ?? 'n/a',
                null !== $gcSubAmount ? $gcSubAmount.'€/month' : 'error',
                $lastConfirmedPayment
                    ? \sprintf('%d€@%s', $lastConfirmedPayment->amount, $lastConfirmedPayment->date->format('Y-m-d'))
                    : 'none',
                $newExpectedAmount,
                $action,
                $result,
            ));

            usleep(self::GOCARDLESS_THROTTLE_MICROSECONDS);

            if ($interactive) {
                if ('SUCCESS' === $result && $subscriptionId) {
                    $this->verifyExecutedActions($io, [[
                        'adherent' => $adherent,
                        'action' => $action,
                        'expected' => $newExpectedAmount,
                        'subscriptionId' => $subscriptionId,
                    ]]);
                }

                break;
            }
        }

        $io->success(\sprintf(
            'Processed %d adherent(s). Corrected: %d. Skipped: %d. Errors: %d.%s',
            $processed,
            $corrected,
            $skipped,
            $errors,
            $dryRun ? ' (dry-run, nothing persisted)' : '',
        ));

        if (!$dryRun && !$interactive && $executed) {
            $this->verifyExecutedActions($io, $executed);
        }

        return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * @param list<array{adherent: Adherent, action: string, expected: int, subscriptionId: ?string}> $executed
     */
    private function verifyExecutedActions(SymfonyStyle $io, array $executed): void
    {
        $io->section('Verification pass — checking GoCardless state after actions');

        $ok = 0;
        $mismatch = 0;
        $errors = 0;

        foreach ($executed as $entry) {
            $adherent = $entry['adherent'];
            $action = $entry['action'];
            $expected = $entry['expected'];
            $subscriptionId = $entry['subscriptionId'];

            if (!$subscriptionId) {
                continue;
            }

            try {
                $subscription = $this->gocardless->getSubscription($subscriptionId);
                $actualAmount = (int) ($subscription->amount / 100);
                $actualStatus = $subscription->status;

                if ('CANCEL' === $action) {
                    $verified = \in_array($actualStatus, Contribution::INACTIVE_SUBSCRIPTION_STATUSES, true);
                    $label = $verified ? 'OK' : 'MISMATCH';
                    $detail = \sprintf('status=%s (expected cancelled/finished)', $actualStatus);
                } else {
                    $verified = $actualAmount === $expected;
                    $label = $verified ? 'OK' : 'MISMATCH';
                    $detail = \sprintf('amount=%d€ (expected %d€)', $actualAmount, $expected);
                }

                $verified ? ++$ok : ++$mismatch;

                $io->writeln(\sprintf(
                    '- %s %s | uuid=%s | subscription=%s | action=%s | verification=%s | %s',
                    $adherent->getFirstName(),
                    $adherent->getLastName(),
                    $adherent->getUuidAsString(),
                    $subscriptionId,
                    $action,
                    $label,
                    $detail,
                ));
            } catch (\Throwable $exception) {
                ++$errors;
                $io->writeln(\sprintf(
                    '- %s %s | uuid=%s | subscription=%s | action=%s | verification=ERROR: %s',
                    $adherent->getFirstName(),
                    $adherent->getLastName(),
                    $adherent->getUuidAsString(),
                    $subscriptionId,
                    $action,
                    $exception->getMessage(),
                ));
            }

            usleep(self::GOCARDLESS_THROTTLE_MICROSECONDS);
        }

        $io->success(\sprintf(
            'Verification: OK=%d | MISMATCH=%d | ERROR=%d',
            $ok,
            $mismatch,
            $errors,
        ));
    }

    /**
     * @param list<string> $excludeUuids
     *
     * @return list<Adherent>
     */
    private function findStaleAdherents(?string $adherentUuid, array $excludeUuids = []): array
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

        if ($excludeUuids) {
            $qb->andWhere('a.uuid NOT IN (:excludeUuids)')->setParameter('excludeUuids', $excludeUuids);
        }

        return $qb->getQuery()->getResult();
    }

    private function findLastConfirmedPayment(Adherent $adherent): ?Payment
    {
        return $this->entityManager->createQueryBuilder()
            ->select('p')
            ->from(Payment::class, 'p')
            ->where('p.adherent = :adherent')
            ->andWhere('p.status IN (:confirmedStatuses)')
            ->setParameter('adherent', $adherent)
            ->setParameter('confirmedStatuses', PaymentStatusEnum::CONFIRMED_PAYMENT_STATUSES)
            ->orderBy('p.date', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    private function fetchGoCardlessSubscriptionAmount(Adherent $adherent): ?int
    {
        $subscriptionId = $adherent->getLastContribution()?->gocardlessSubscriptionId;

        if (!$this->looksLikeValidSubscriptionId($subscriptionId)) {
            return null;
        }

        try {
            $subscription = $this->gocardless->getSubscription($subscriptionId);

            return (int) ($subscription->amount / 100);
        } catch (\Throwable $exception) {
            $this->logger->warning('Failed to fetch GoCardless subscription', [
                'subscription_id' => $subscriptionId,
                'exception' => $exception,
            ]);

            return null;
        }
    }

    private function looksLikeValidSubscriptionId(?string $subscriptionId): bool
    {
        return null !== $subscriptionId && 1 === preg_match('/^SB[A-Z0-9]{12}$/', $subscriptionId);
    }
}
