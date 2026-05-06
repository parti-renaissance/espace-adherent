<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Audience\Handler;

use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\Audience\AudienceCheckCalculator;
use App\Mailchimp\Campaign\Audience\Message\FinalizeCampaignAudienceMessage;
use App\Mailchimp\Campaign\Audience\PreparationStatusEnum;
use App\Mailchimp\Campaign\Audience\TargetedProcessingStatusEnum;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Mailchimp\Driver;
use App\Repository\AdherentMessage\AdherentMessageTargetedRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class FinalizeCampaignAudienceHandler
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdherentMessageTargetedRepository $targetedRepository,
        private readonly Driver $driver,
        private readonly MailchimpObjectIdMapping $mailchimpObjectIdMapping,
        private readonly AudienceCheckCalculator $audienceCheckCalculator,
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

        // Idempotent: a previous dispatch already completed.
        if (PreparationStatusEnum::Ready === $campaign->getPreparationStatus()) {
            return;
        }

        if ($campaign->isCancellationRequested()) {
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

        $messageId = $campaign->getMessage()->getId();

        // Premature finalize: chunks not all done yet. Subsequent chunk workers will re-dispatch.
        if ($this->targetedRepository->existsPending($messageId)) {
            return;
        }

        $listId = $this->mailchimpObjectIdMapping->getMainListId();

        $segmentData = $this->driver->getSegment($segmentId, $listId);
        $prepared = (int) ($segmentData['member_count'] ?? 0);

        $counts = $this->targetedRepository->aggregateStatusCounts($messageId);
        $staticSegment->preparedCount = $counts[TargetedProcessingStatusEnum::Added->value] ?? 0;
        $staticSegment->refusedCount = $counts[TargetedProcessingStatusEnum::Refused->value] ?? 0;
        $staticSegment->erroredCount = $counts[TargetedProcessingStatusEnum::Errored->value] ?? 0;
        $staticSegment->builtAt = new \DateTimeImmutable();

        $expected = $staticSegment->expectedCount ?? 0;
        $audienceCheck = $this->audienceCheckCalculator->compute($expected, $prepared);

        $campaign->markAsReady($audienceCheck);
        $this->entityManager->flush();
    }
}
