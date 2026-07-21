<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Audience\Handler;

use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\Audience\BlockReasonEnum;
use App\Mailchimp\Campaign\Audience\Message\FinalizeCampaignAudienceMessage;
use App\Mailchimp\Campaign\Audience\PreparationStatusEnum;
use App\Mailchimp\Campaign\Audience\SegmentMemberStatusEnum;
use App\Mailchimp\Campaign\Command\SendMailchimpCampaignCommand;
use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
use App\Ses\Campaign\Message\TriggerSesCampaignMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[AsMessageHandler]
class FinalizeCampaignAudienceHandler
{
    private LoggerInterface $logger;

    /**
     * @param int $maxErroredChunks  how many whole chunks may fail their push before the send is blocked
     * @param int $maxErroredPercent share of the audience that may be errored before the send is blocked;
     *                               deliberately bound to the send-time recipient undershoot knob — both
     *                               answer "how much of this audience may be missing before we refuse to send"
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MailchimpStaticSegmentMemberRepository $memberRepository,
        private readonly MessageBusInterface $bus,
        private readonly int $maxErroredChunks,
        private readonly int $maxErroredPercent,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function __invoke(FinalizeCampaignAudienceMessage $message): void
    {
        $campaign = $this->entityManager->find(MailchimpCampaign::class, $message->mailchimpCampaignId);
        if (!$campaign) {
            $this->logger->warning('[AudienceFinalize] MailchimpCampaign not found', [
                'campaign_id' => $message->mailchimpCampaignId,
            ]);

            return;
        }

        $this->entityManager->refresh($campaign);

        // Re-entry path: a prior finalize already marked Ready, but pendingSend is still set
        // (auto-send dispatch failed previously). Replay only the dispatch step.
        if (PreparationStatusEnum::Ready === $campaign->getPreparationStatus()) {
            $this->dispatchAutoSendIfNeeded($campaign);
            $this->entityManager->flush();

            return;
        }

        if (PreparationStatusEnum::Preparing !== $campaign->getPreparationStatus()) {
            $this->logger->warning('[AudienceFinalize] Unexpected status, skipping', [
                'campaign_id' => $campaign->getId(),
                'status' => $campaign->getPreparationStatus()->value,
            ]);

            return;
        }

        $staticSegment = $campaign->getMailchimpStaticSegment();
        if (null === $staticSegment) {
            $this->logger->error('[AudienceFinalize] Static segment missing', ['campaign_id' => $campaign->getId()]);

            return;
        }

        $staticSegmentId = $staticSegment->id;

        // Premature finalize: chunks not all done yet. Subsequent chunk workers will re-dispatch.
        if ($this->memberRepository->existsPending($staticSegmentId)) {
            return;
        }

        $counts = $this->memberRepository->aggregateStatusCounts($staticSegmentId);
        $preparedCount = $counts[SegmentMemberStatusEnum::Added->value] ?? 0;
        $refusedCount = $counts[SegmentMemberStatusEnum::Refused->value] ?? 0;
        $erroredCount = $counts[SegmentMemberStatusEnum::Errored->value] ?? 0;

        $staticSegment->preparedCount = $preparedCount;
        $staticSegment->refusedCount = $refusedCount;
        $staticSegment->erroredCount = $erroredCount;

        // Errored chunks are infrastructure push failures (NOT legitimate refusals): the segment is
        // incomplete through no fault of the audience. Blocking outright was too blunt though — a single
        // hiccuping chunk (500 rows, 0.37% of a 134k audience) held back the whole campaign twice on
        // 2026-07-16. Tolerate a bounded amount of infrastructure noise, block anything beyond it.
        //
        // Two bounds, because neither alone is right:
        //  - chunks: the real unit of failure (Errored is only ever set per whole chunk). Caps the raw
        //    number of people silently dropped, whatever the audience size.
        //  - percent: guards small audiences, where one chunk is a large slice (a chunk is 10% of a
        //    5k send but 0.37% of a 134k one) — an amputated audience must never leave silently.
        if ($erroredCount > 0) {
            $erroredChunks = $this->memberRepository->countErroredChunks($staticSegmentId);
            $maxErroredRows = (int) floor(($staticSegment->expectedCount ?? 0) * $this->maxErroredPercent / 100);

            $context = [
                'campaign_id' => $campaign->getId(),
                'errored_count' => $erroredCount,
                'errored_chunks' => $erroredChunks,
                'max_errored_chunks' => $this->maxErroredChunks,
                'max_errored_rows' => $maxErroredRows,
                'prepared_count' => $preparedCount,
                'expected_count' => $staticSegment->expectedCount,
            ];

            if ($erroredChunks > $this->maxErroredChunks || $erroredCount > $maxErroredRows) {
                $this->logger->error('[AudienceFinalize] Send blocked: preparation errors over tolerance', $context);

                $campaign->markAsFailed(BlockReasonEnum::PreparationErrors);
                $this->entityManager->flush();

                return;
            }

            $this->logger->warning('[AudienceFinalize] Preparation errors within tolerance, proceeding', $context);
        }

        $stagedCount = $preparedCount + $refusedCount + $erroredCount;

        if ($stagedCount !== $staticSegment->expectedCount || 0 === $preparedCount) {
            $this->logger->error('[AudienceFinalize] Send blocked: audience incomplete or empty', [
                'campaign_id' => $campaign->getId(),
                'expected_count' => $staticSegment->expectedCount,
                'staged_count' => $stagedCount,
                'prepared_count' => $preparedCount,
            ]);

            $campaign->markAsFailed(BlockReasonEnum::Empty);
            $this->entityManager->flush();

            return;
        }

        $builtAt = new \DateTimeImmutable();
        $staticSegment->builtAt = $builtAt;
        if (null !== $staticSegment->buildStartedAt) {
            $staticSegment->buildDurationMs = (int) (($builtAt->format('U.u') - $staticSegment->buildStartedAt->format('U.u')) * 1000);
        }

        $campaign->markAsReady();
        $this->entityManager->flush();

        $this->dispatchAutoSendIfNeeded($campaign);
        $this->entityManager->flush();
    }

    /**
     * Dispatches the deferred auto-send if the campaign asked for it.
     *
     * SES (default): TriggerSesCampaignMessage, no DelayStamp — the audience is already complete
     * in the DB at finalize time, so the fan-out can start immediately.
     *
     * Mailchimp fallback: SendMailchimpCampaignCommand with a 60s DelayStamp, giving the remote
     * static segment time to propagate the members pushed during preparation before the send.
     *
     * Order is intentional: dispatch first, then clearPendingSend. If the dispatch raises
     * (broker down, transport error), pendingSend stays true so the Messenger retry of
     * the FinalizeCampaignAudienceMessage re-enters via the Ready guard and replays.
     */
    private function dispatchAutoSendIfNeeded(MailchimpCampaign $campaign): void
    {
        if (!$campaign->isPendingSend()) {
            return;
        }

        if (!$campaign->canSend()) {
            $this->logger->error('[AudienceFinalize] Auto-send blocked: cannot send after preparation', [
                'campaign_id' => $campaign->getId(),
                'preparation_status' => $campaign->getPreparationStatus()->value,
                'block_reason' => $campaign->getBlockReason()?->value,
            ]);
            $campaign->clearPendingSend();

            return;
        }

        try {
            if ($campaign->sendViaMailchimp) {
                $this->bus->dispatch(new SendMailchimpCampaignCommand((int) $campaign->getId()), [new DelayStamp(60_000)]);
            } else {
                $this->bus->dispatch(new TriggerSesCampaignMessage((int) $campaign->getId()));
            }
            $campaign->clearPendingSend();
        } catch (\Throwable $e) {
            $this->logger->error('[AudienceFinalize] Auto-send dispatch failed', [
                'campaign_id' => $campaign->getId(),
                'exception' => $e,
            ]);

            throw $e;
        }
    }
}
