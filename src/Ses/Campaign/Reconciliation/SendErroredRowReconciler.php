<?php

declare(strict_types=1);

namespace App\Ses\Campaign\Reconciliation;

use App\Entity\AdherentMessage\MailchimpStaticSegmentMember;
use App\Mailchimp\Campaign\Audience\SegmentMemberStatusEnum;
use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
use App\Ses\Campaign\Message\ReconcileSendErroredRowMessage;
use App\Ses\Campaign\Reach\CampaignReachInserter;
use App\Ses\Stats\Command\RefreshSesPublicationStatsCommand;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

class SendErroredRowReconciler
{
    /** Grace before the first reconciliation look: the webhook is async, an event needs time to be attributed. */
    private const int RECONCILE_GRACE_MS = 30 * 60 * 1_000;

    private LoggerInterface $logger;

    public function __construct(
        private readonly MailchimpStaticSegmentMemberRepository $memberRepository,
        private readonly CampaignReachInserter $reachInserter,
        private readonly MessageBusInterface $bus,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Arms the timed fallback for a freshly quarantined row.
     *
     * A dispatch failure is logged, not propagated: since an SES event now promotes the row on arrival, losing
     * this message only costs the late "never confirmed" alert, never the promotion itself.
     */
    public function arm(int $rowId): void
    {
        try {
            $this->bus->dispatch(
                new ReconcileSendErroredRowMessage($rowId),
                [new DelayStamp(self::RECONCILE_GRACE_MS)],
            );
        } catch (\Throwable $dispatchFailure) {
            $this->logger->warning('[SES][Campaign] Could not arm the reconciliation of a quarantined row', [
                'row' => $rowId,
                'exception' => $dispatchFailure,
            ]);
        }
    }

    /**
     * Promotes the member's quarantined row, if it has one. Called by the webhook right after an SES event was
     * attributed to the member: the event is the proof the send did happen, so the row can leave quarantine at
     * once instead of waiting for a timer.
     */
    public function promoteForMember(int $messageId, int $adherentId): void
    {
        $rowId = $this->memberRepository->findSendErroredRowIdForMember($messageId, $adherentId);
        if (null === $rowId) {
            return;
        }

        $this->reconcile($rowId);
    }

    /**
     * @return bool true when the row needs no further watching (promoted, already resolved, or gone),
     *              false when it stays quarantined, still waiting for an SES event
     */
    public function reconcile(int $rowId): bool
    {
        // Read BEFORE the update: a DQL update does not refresh the identity map, so an entity re-read
        // afterwards could be stale. The post-update status is derived from the affected-row count instead.
        $row = $this->memberRepository->find($rowId);
        if (!$row instanceof MailchimpStaticSegmentMember) {
            return true;
        }

        $promoted = $this->memberRepository->promoteSendErroredRowToSent($rowId) > 0;

        if (!$promoted && SegmentMemberStatusEnum::Sent !== $row->processingStatus) {
            return false;
        }

        // The reach is NOT conditioned on "just promoted": a crash between the promotion and this insert would
        // lose it for good, since the guarded update no longer matches on the redelivery. INSERT IGNORE makes
        // the replay free. An adherent deleted since the send (SET NULL) has no reach to record.
        $campaign = $row->staticSegment->campaign;
        $message = $campaign->getMessage();

        if (null !== $row->adherent) {
            $this->reachInserter->insertOne((int) $message->getId(), (int) $row->adherent->getId());
        }

        if ($promoted) {
            // The publication stats are materialized (PublicationStatsRefresher writes PublicationStatistics)
            // and the refresh chain started at completion has already stopped: a late promotion needs its own
            // one-off refresh, without restarting that chain.
            $this->bus->dispatch(new RefreshSesPublicationStatsCommand($message->getUuid(), autoReschedule: false));

            $this->logger->info('[SES][Campaign] Quarantined row promoted — an SES event proved the send happened', [
                'campaign_id' => $campaign->getId(),
                'row' => $rowId,
            ]);
        }

        return true;
    }
}
