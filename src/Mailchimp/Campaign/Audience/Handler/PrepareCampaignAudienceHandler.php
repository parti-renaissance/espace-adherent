<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Audience\Handler;

use App\Doctrine\Utils\BulkInsertHelper;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpStaticSegment;
use App\Mailchimp\Campaign\Audience\BlockReasonEnum;
use App\Mailchimp\Campaign\Audience\Message\FinalizeCampaignAudienceMessage;
use App\Mailchimp\Campaign\Audience\Message\PrepareCampaignAudienceMessage;
use App\Mailchimp\Campaign\Audience\PreparationStatusEnum;
use App\Mailchimp\Campaign\Audience\SegmentMemberStatusEnum;
use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[AsMessageHandler]
class PrepareCampaignAudienceHandler
{
    private const int PUSH_CHUNK_SIZE = 50;
    private const int MEMBER_INSERT_BATCH = 5_000;

    private LoggerInterface $logger;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdherentRepository $adherentRepository,
        private readonly MailchimpStaticSegmentMemberRepository $memberRepository,
        private readonly BulkInsertHelper $bulkInsertHelper,
        private readonly MessageBusInterface $bus,
        private readonly NormalizerInterface $normalizer,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function __invoke(PrepareCampaignAudienceMessage $message): void
    {
        $campaign = $this->entityManager->find(MailchimpCampaign::class, $message->mailchimpCampaignId);
        if (!$campaign) {
            $this->logger->warning('MailchimpCampaign not found', ['id' => $message->mailchimpCampaignId]);

            return;
        }

        // Skip when the segment is Ready and nobody asked for a (re-)send: the message is most
        // likely a residual Messenger redelivery. A pending-send forces the rebuild path even on
        // Ready state (e.g. a Ready segment that must be rebuilt after the user re-clicks Send).
        if (!$campaign->isPendingSend()
            && PreparationStatusEnum::Ready === $campaign->getPreparationStatus()
        ) {
            return;
        }

        $staticSegment = $campaign->getMailchimpStaticSegment();
        if (null === $staticSegment) {
            $this->logger->error('Static segment not initialised before /prepare', ['campaign_id' => $campaign->getId()]);
            $this->failPreparation($campaign, BlockReasonEnum::MailchimpUnavailable);
            $this->entityManager->flush();

            return;
        }

        if (PreparationStatusEnum::Preparing === $campaign->getPreparationStatus()
            && null !== $staticSegment->chunksTotal
            && $staticSegment->chunksTotal > 0
        ) {
            $this->redispatchFinalize($campaign);

            return;
        }

        $lockedBy = $this->entityManager->find(Adherent::class, $message->lockedById);
        if (null === $lockedBy) {
            $this->logger->error('Locking adherent not found', ['adherent_id' => $message->lockedById, 'campaign_id' => $campaign->getId()]);
            $this->failPreparation($campaign, BlockReasonEnum::MailchimpUnavailable);
            $staticSegment->errorSummary = \sprintf('Locking adherent %d not found.', $message->lockedById);
            $this->entityManager->flush();

            return;
        }

        $campaign->markAsPreparing($lockedBy);
        $this->resetTrackingFields($staticSegment);
        $this->captureFilterSnapshot($staticSegment, $campaign);
        $this->entityManager->flush();

        try {
            // Wipe any residual rows from a previous (legacy) preparation before bulk-inserting.
            $this->memberRepository->deleteBySegmentId($staticSegment->id);

            $expected = $this->loadAndInsertMembers($campaign);
            $staticSegment->expectedCount = $expected;
            $this->entityManager->flush();

            if (0 === $expected) {
                $this->failPreparation($campaign, BlockReasonEnum::Empty);
                $staticSegment->errorSummary = 'SQL audience returned 0 valid emails.';
                $this->entityManager->flush();

                return;
            }

            $chunksTotal = (int) ceil($expected / self::PUSH_CHUNK_SIZE);
            $staticSegment->chunksTotal = $chunksTotal;

            $this->entityManager->flush();

            $this->bus->dispatch(new FinalizeCampaignAudienceMessage($campaign->getId()));
        } catch (\Throwable $e) {
            $this->logger->error('Audience preparation failed', [
                'campaign_id' => $message->mailchimpCampaignId,
                'exception' => $e,
            ]);
            $this->failPreparation($campaign, BlockReasonEnum::MailchimpUnavailable);
            $staticSegment->errorSummary = $e->getMessage();
            $this->entityManager->flush();
            throw $e;
        }
    }

    private function failPreparation(MailchimpCampaign $campaign, BlockReasonEnum $reason): void
    {
        if ($campaign->isPendingSend()) {
            $this->logger->error('[AudiencePrepare] Auto-send aborted: preparation failed', [
                'campaign_id' => $campaign->getId(),
                'block_reason' => $reason->value,
            ]);
        }

        $campaign->markAsFailed($reason);
    }

    private function redispatchFinalize(MailchimpCampaign $campaign): void
    {
        $this->bus->dispatch(new FinalizeCampaignAudienceMessage($campaign->getId()));
    }

    private function resetTrackingFields(MailchimpStaticSegment $staticSegment): void
    {
        ++$staticSegment->attempts;
        $staticSegment->buildStartedAt = new \DateTimeImmutable();
        $staticSegment->builtAt = null;
        $staticSegment->buildDurationMs = null;
        $staticSegment->preparedCount = null;
        $staticSegment->refusedCount = null;
        $staticSegment->erroredCount = null;
        $staticSegment->chunksTotal = null;
        $staticSegment->chunksDone = 0;
        $staticSegment->errorSummary = null;
    }

    private function loadAndInsertMembers(MailchimpCampaign $campaign): int
    {
        $staticSegmentId = $campaign->getMailchimpStaticSegment()->id;
        $now = new \DateTimeImmutable()->format('Y-m-d H:i:s');
        $adherentIds = $this->adherentRepository->findAdherentIdsForMessage($campaign->getMessage());

        foreach (array_chunk($adherentIds, self::MEMBER_INSERT_BATCH, true) as $batch) {
            $this->insertMemberBatch($staticSegmentId, $batch, $now);
        }

        return \count($adherentIds);
    }

    /**
     * @param array<int, int> $batch index (= absolute row position) => adherent id
     */
    private function insertMemberBatch(int $staticSegmentId, array $batch, string $now): void
    {
        $rows = [];
        foreach ($batch as $index => $adherentId) {
            $rows[] = [
                'static_segment_id' => $staticSegmentId,
                'adherent_id' => $adherentId,
                'chunk_number' => intdiv($index, self::PUSH_CHUNK_SIZE) + 1,
                'processing_status' => SegmentMemberStatusEnum::Added->value,
                'created_at' => $now,
            ];
        }

        $this->bulkInsertHelper->insertIgnore('mailchimp_static_segment_member', $rows);
    }

    private function captureFilterSnapshot(MailchimpStaticSegment $segment, MailchimpCampaign $campaign): void
    {
        $message = $campaign->getMessage();
        $filter = method_exists($message, 'getFilter') ? $message->getFilter() : null;

        if (null === $filter) {
            $segment->filterSnapshot = null;
            $segment->filterHash = null;

            return;
        }

        $this->entityManager->initializeObject($filter);

        $snapshot = $this->normalizer->normalize($filter, 'json', ['groups' => ['adherent_message_read_filter']]);
        ksort($snapshot);

        $segment->filterSnapshot = $snapshot;
        $segment->filterHash = hash('sha256', json_encode($snapshot, \JSON_THROW_ON_ERROR));
    }
}
