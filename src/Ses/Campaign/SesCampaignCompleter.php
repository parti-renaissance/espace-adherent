<?php

declare(strict_types=1);

namespace App\Ses\Campaign;

use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
use App\Repository\MailchimpCampaignRepository;
use App\Ses\Campaign\Reach\CampaignReachInserter;
use App\Ses\Stats\Command\RefreshSesPublicationStatsCommand;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

/**
 * Closes a SES campaign once no sendable row is left in the whole segment (Sending -> Sent) and records the
 * reach. Both steps are idempotent — the status flip is an atomic guarded update only one caller can win, and
 * the reach insert is an INSERT IGNORE — so two chunks finishing together, or the reaper racing the last
 * chunk, complete the campaign exactly once.
 *
 * Shared by the chunk handler (the normal path) and the stale-row reaper (which unblocks a campaign whose
 * last chunk died before it could complete it).
 */
class SesCampaignCompleter
{
    /** Let the last sends settle before the first stats snapshot. */
    private const int STATS_REFRESH_DELAY_MS = 300_000;

    private LoggerInterface $logger;

    public function __construct(
        private readonly MailchimpCampaignRepository $campaignRepository,
        private readonly MailchimpStaticSegmentMemberRepository $memberRepository,
        private readonly CampaignReachInserter $reachInserter,
        private readonly MessageBusInterface $bus,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function completeIfDone(MailchimpCampaign $campaign, int $staticSegmentId): void
    {
        if (0 !== $this->memberRepository->countRemainingToSend($staticSegmentId)) {
            return;
        }

        // Atomic Sending -> Sent: only the caller that wins it records the reach, exactly once.
        if (!$this->campaignRepository->completeSending((int) $campaign->getId())) {
            return;
        }

        $this->reachInserter->insertFromSentRows($staticSegmentId, (int) $campaign->getMessage()->getId());

        $this->logger->info('[SES][Campaign] Campaign send complete', [
            'campaign_id' => $campaign->getId(),
        ]);

        $this->bus->dispatch(
            new RefreshSesPublicationStatsCommand($campaign->getMessage()->getUuid()),
            [new DelayStamp(self::STATS_REFRESH_DELAY_MS)]
        );
    }
}
