<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Audience\Handler;

use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\Audience\Message\FinalizeCampaignAudienceMessage;
use App\Mailchimp\Campaign\Audience\PreparationStatusEnum;
use App\Mailchimp\Campaign\Audience\SegmentMemberStatusEnum;
use App\Mailchimp\Campaign\Command\SendMailchimpCampaignCommand;
use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class FinalizeCampaignAudienceHandler
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MailchimpStaticSegmentMemberRepository $memberRepository,
        private readonly MessageBusInterface $bus,
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

        $segmentId = $campaign->getStaticSegmentId();
        $staticSegment = $campaign->getMailchimpStaticSegment();
        if (null === $segmentId || null === $staticSegment) {
            $this->logger->error('[AudienceFinalize] Static segment missing', ['campaign_id' => $campaign->getId()]);

            return;
        }

        $staticSegmentId = $staticSegment->id;

        // Premature finalize: chunks not all done yet. Subsequent chunk workers will re-dispatch.
        if ($this->memberRepository->existsPending($staticSegmentId)) {
            return;
        }

        $counts = $this->memberRepository->aggregateStatusCounts($staticSegmentId);
        $staticSegment->preparedCount = $counts[SegmentMemberStatusEnum::Added->value] ?? 0;
        $staticSegment->refusedCount = $counts[SegmentMemberStatusEnum::Refused->value] ?? 0;
        $staticSegment->erroredCount = $counts[SegmentMemberStatusEnum::Errored->value] ?? 0;

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
     * Dispatches the deferred SendMailchimpCampaignCommand if the campaign asked for it.
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
            $this->bus->dispatch(new SendMailchimpCampaignCommand($campaign->getId()));
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
