<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Mailchimp\Driver;
use App\Repository\AdherentMessageTargetedRepository;
use App\Repository\MailchimpCampaignRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:mailchimp:segment:cleanup',
    description: 'Supprime les static segments Mailchimp expirés et orphelins, purge l\'audit adherent_message_targeted.',
)]
class MailchimpCleanupStaticSegmentsCommand extends Command
{
    public const string SEGMENT_NAME_PREFIX = 'campaign_';
    public const int EXPIRED_RETENTION_DAYS = 7;
    public const int ORPHAN_THRESHOLD_HOURS = 24;
    public const int TARGETED_RETENTION_DAYS = 365;

    private LoggerInterface $logger;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MailchimpCampaignRepository $campaignRepository,
        private readonly AdherentMessageTargetedRepository $targetedRepository,
        private readonly MailchimpObjectIdMapping $mailchimpObjectIdMapping,
        private readonly Driver $driver,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $listId = $this->mailchimpObjectIdMapping->getMainListId();

        $io->section('Étape 1 — Suppression des segments de campagnes Sent expirées');
        $expiredCount = $this->cleanupExpiredCampaignSegments($io);
        $io->success(\sprintf('%d segment(s) de campagnes expirées supprimé(s).', $expiredCount));

        $io->section('Étape 2 — Suppression des segments orphelins > 24h');
        $orphanCount = $this->cleanupOrphanSegments($io, $listId);
        $io->success(\sprintf('%d segment(s) orphelin(s) supprimé(s).', $orphanCount));

        $io->section('Étape 3 — Purge adherent_message_targeted > 1 an');
        $purgedCount = $this->purgeOldTargeted();
        $io->success(\sprintf('%d ligne(s) adherent_message_targeted purgée(s).', $purgedCount));

        return Command::SUCCESS;
    }

    private function cleanupExpiredCampaignSegments(SymfonyStyle $io): int
    {
        $campaigns = $this->campaignRepository->findExpiredForCleanup(self::EXPIRED_RETENTION_DAYS);
        $count = 0;

        foreach ($campaigns as $campaign) {
            $segmentId = $campaign->getStaticSegmentId();
            if (null === $segmentId) {
                continue;
            }

            try {
                $this->driver->deleteStaticSegment($segmentId);
                $this->resetCampaignSegmentRefs($campaign);
                ++$count;
            } catch (\Throwable $e) {
                $this->logger->warning('Cleanup static segment échoué', [
                    'campaign_id' => $campaign->getId(),
                    'segment_id' => $segmentId,
                    'error' => $e->getMessage(),
                ]);
                $io->warning(\sprintf('Échec suppression segment %d (campaign #%d) : %s', $segmentId, (int) $campaign->getId(), $e->getMessage()));
            }
        }

        return $count;
    }

    private function cleanupOrphanSegments(SymfonyStyle $io, string $listId): int
    {
        $threshold = new \DateTimeImmutable()->modify(\sprintf('-%d hours', self::ORPHAN_THRESHOLD_HOURS));
        $count = 0;

        foreach ($this->driver->getAllSegmentsWithPrefix(self::SEGMENT_NAME_PREFIX, $listId) as $segment) {
            $segmentId = (int) ($segment['id'] ?? 0);
            if ($segmentId <= 0) {
                continue;
            }

            $createdAtRaw = $segment['created_at'] ?? null;
            if (null === $createdAtRaw) {
                continue;
            }

            try {
                $createdAt = new \DateTimeImmutable($createdAtRaw);
            } catch (\Exception) {
                continue;
            }

            if ($createdAt > $threshold) {
                continue;
            }

            if ($this->campaignRepository->isLinkedToActiveCampaign($segmentId)) {
                continue;
            }

            try {
                $this->driver->deleteStaticSegment($segmentId);
                ++$count;
            } catch (\Throwable $e) {
                $this->logger->warning('Cleanup segment orphelin échoué', [
                    'segment_id' => $segmentId,
                    'error' => $e->getMessage(),
                ]);
                $io->warning(\sprintf('Échec suppression segment orphelin %d : %s', $segmentId, $e->getMessage()));
            }
        }

        return $count;
    }

    private function purgeOldTargeted(): int
    {
        $threshold = new \DateTimeImmutable()->modify(\sprintf('-%d days', self::TARGETED_RETENTION_DAYS));

        return $this->targetedRepository->deleteForMessagesSentBefore($threshold);
    }

    private function resetCampaignSegmentRefs(MailchimpCampaign $campaign): void
    {
        $campaign->setStaticSegmentId(null);
        $campaign->setMailchimpSegmentName(null);
        $campaign->setDeleteSegmentAt(null);
        $this->entityManager->flush();
    }
}
