<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Audience\Handler;

use App\Doctrine\Utils\BulkInsertHelper;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpStaticSegment;
use App\Mailchimp\Campaign\Audience\BlockReasonEnum;
use App\Mailchimp\Campaign\Audience\Message\FinalizeCampaignAudienceMessage;
use App\Mailchimp\Campaign\Audience\Message\PrepareCampaignAudienceMessage;
use App\Mailchimp\Campaign\Audience\Message\ProcessAudienceChunkMessage;
use App\Mailchimp\Campaign\Audience\PreparationStatusEnum;
use App\Mailchimp\Campaign\Audience\TargetedProcessingStatusEnum;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Mailchimp\Campaign\MailchimpStaticSegmentServiceInterface;
use App\Repository\AdherentMessage\AdherentMessageTargetedRepository;
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
    private const int TOO_LARGE_THRESHOLD = 500_000;
    private const int PUSH_CHUNK_SIZE = 500;
    private const int TARGETED_INSERT_BATCH = 5_000;

    private LoggerInterface $logger;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MailchimpStaticSegmentServiceInterface $staticSegmentService,
        private readonly MailchimpObjectIdMapping $mailchimpObjectIdMapping,
        private readonly AdherentRepository $adherentRepository,
        private readonly AdherentMessageTargetedRepository $targetedRepository,
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

        if ($this->isAlreadyPreparedAndFresh($campaign)) {
            return;
        }

        $segmentId = $campaign->getStaticSegmentId();
        $staticSegment = $campaign->getMailchimpStaticSegment();
        if (null === $segmentId || null === $staticSegment) {
            $this->logger->error('Static segment not initialised before /prepare', ['campaign_id' => $campaign->getId()]);
            $campaign->markAsFailed(BlockReasonEnum::MailchimpUnavailable, 'Static segment was not initialised before /prepare.');
            $this->entityManager->flush();

            return;
        }

        $listId = $this->mailchimpObjectIdMapping->getMainListId();

        // === Idempotence retry ===
        // chunksTotal is committed only after the segment reset + bulk insert. If it's > 0 we are
        // resuming a previously-started preparation; redispatch only the chunks that still have
        // pending rows + finalize (the latter is idempotent).
        if (PreparationStatusEnum::Preparing === $campaign->getPreparationStatus()
            && null !== $staticSegment->chunksTotal
            && $staticSegment->chunksTotal > 0
        ) {
            $this->redispatchPendingChunks($campaign);

            return;
        }

        $campaign->markAsPreparing($message->lockedBy);
        $this->resetTrackingFields($staticSegment);
        $this->captureFilterSnapshot($staticSegment, $campaign);
        $this->entityManager->flush();

        try {
            $messageId = $campaign->getMessage()->getId();

            // Wipe any residual rows from a previous (legacy) preparation before bulk-inserting.
            $this->targetedRepository->deleteByMessageId($messageId);

            $expected = $this->streamAndInsertTargeted($campaign);
            $staticSegment->expectedCount = $expected;
            $this->entityManager->flush();

            if (0 === $expected) {
                $campaign->markAsFailed(BlockReasonEnum::Empty, 'SQL audience returned 0 valid emails.');
                $this->entityManager->flush();

                return;
            }

            if ($expected > self::TOO_LARGE_THRESHOLD) {
                $campaign->markAsFailed(BlockReasonEnum::TooLarge, \sprintf('Audience %d > threshold %d.', $expected, self::TOO_LARGE_THRESHOLD));
                $this->entityManager->flush();

                return;
            }

            $chunksTotal = (int) ceil($expected / self::PUSH_CHUNK_SIZE);
            $staticSegment->chunksTotal = $chunksTotal;
            // Point of no return for retry idempotence: from now on, the orchestrator skips the
            // reset+insert path and only redispatches still-pending chunks.
            $this->entityManager->flush();

            // Reset Mailchimp segment members (idempotent: PATCH with [] starts from a clean slate).
            $this->staticSegmentService->update($segmentId, [], $listId);

            $campaignId = $campaign->getId();
            for ($n = 0; $n < $chunksTotal; ++$n) {
                $this->bus->dispatch(new ProcessAudienceChunkMessage($campaignId, $n));
            }

            // Safety net: if no chunk worker ever fires the finalize (e.g. all chunks fail before
            // running their EXISTS pending check), the failure subscriber will dispatch its own.
            // This explicit dispatch is redundant on the happy path (handler is idempotent).
            $this->bus->dispatch(new FinalizeCampaignAudienceMessage($campaignId));
        } catch (\Throwable $e) {
            $this->logger->error('Audience preparation failed', [
                'campaign_id' => $message->mailchimpCampaignId,
                'exception' => $e,
            ]);
            $campaign->markAsFailed(BlockReasonEnum::MailchimpUnavailable, $e->getMessage());
            $staticSegment->errorSummary = $e->getMessage();
            $this->entityManager->flush();
            throw $e; // let Messenger handle retry/DLQ
        }
    }

    private function redispatchPendingChunks(MailchimpCampaign $campaign): void
    {
        $messageId = $campaign->getMessage()->getId();
        $pendingChunks = $this->targetedRepository->findChunksWithPending($messageId);
        $campaignId = $campaign->getId();

        foreach ($pendingChunks as $chunkNumber) {
            $this->bus->dispatch(new ProcessAudienceChunkMessage($campaignId, $chunkNumber));
        }

        // Finalize handler is idempotent — guards on Ready / EXISTS pending — safe to dispatch.
        $this->bus->dispatch(new FinalizeCampaignAudienceMessage($campaignId));
    }

    private function resetTrackingFields(MailchimpStaticSegment $staticSegment): void
    {
        ++$staticSegment->attempts;
        $staticSegment->builtAt = null;
        $staticSegment->buildDurationMs = null;
        $staticSegment->preparedCount = null;
        $staticSegment->refusedCount = null;
        $staticSegment->erroredCount = null;
        $staticSegment->chunksTotal = null;
        $staticSegment->chunksDone = 0;
        $staticSegment->errorSummary = null;
    }

    /**
     * Streams emails from the audience query, batches them, and bulk-inserts targeted rows
     * with chunk_number assigned on the fly. Avoids materialising the full email list in RAM.
     */
    private function streamAndInsertTargeted(MailchimpCampaign $campaign): int
    {
        $messageId = $campaign->getMessage()->getId();
        $now = new \DateTimeImmutable()->format('Y-m-d H:i:s');
        $expected = 0;
        $bufferEmails = [];

        foreach ($this->adherentRepository->findAdherentEmailsForMessage($campaign->getMessage()) as $email) {
            $bufferEmails[$expected] = $email;
            ++$expected;

            if (\count($bufferEmails) >= self::TARGETED_INSERT_BATCH) {
                $this->insertTargetedBatch($messageId, $bufferEmails, $now);
                $bufferEmails = [];
            }
        }

        if ($bufferEmails) {
            $this->insertTargetedBatch($messageId, $bufferEmails, $now);
        }

        return $expected;
    }

    /**
     * @param array<int, string> $bufferEmails index (= absolute row position) => email
     */
    private function insertTargetedBatch(int $messageId, array $bufferEmails, string $now): void
    {
        $emailToAdherentId = $this->adherentRepository->mapIdsByEmails(array_values($bufferEmails));

        $rows = [];
        foreach ($bufferEmails as $index => $email) {
            $rows[] = [
                'message_id' => $messageId,
                'adherent_id' => $emailToAdherentId[$email] ?? null,
                'chunk_number' => intdiv($index, self::PUSH_CHUNK_SIZE),
                'processing_status' => TargetedProcessingStatusEnum::Pending->value,
                'targeted_at' => $now,
            ];
        }

        $this->bulkInsertHelper->insertIgnore('adherent_message_targeted', $rows);
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

        // Reuse the same shape exposed by the API (#[ApiResource] normalizationContext
        // on AdherentMessageFilter) so the snapshot mirrors what the front sees.
        $snapshot = $this->normalizer->normalize($filter, 'json', ['groups' => ['adherent_message_read_filter']]);
        ksort($snapshot);

        $segment->filterSnapshot = $snapshot;
        $segment->filterHash = hash('sha256', json_encode($snapshot, \JSON_THROW_ON_ERROR));
    }

    private function isAlreadyPreparedAndFresh(MailchimpCampaign $campaign): bool
    {
        if (PreparationStatusEnum::Ready !== $campaign->getPreparationStatus()) {
            return false;
        }

        $message = $campaign->getMessage();
        $filter = method_exists($message, 'getFilter') ? $message->getFilter() : null;
        $filterUpdatedAt = $filter && method_exists($filter, 'getUpdatedAt') ? $filter->getUpdatedAt() : null;
        $preparedAt = $campaign->getPreparedAt();

        return null !== $filterUpdatedAt
            && null !== $preparedAt
            && $filterUpdatedAt < $preparedAt;
    }
}
