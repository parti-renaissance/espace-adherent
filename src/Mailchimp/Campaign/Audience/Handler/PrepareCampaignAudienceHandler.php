<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Audience\Handler;

use App\Doctrine\Utils\BulkInsertHelper;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpStaticSegment;
use App\Mailchimp\Campaign\Audience\AudienceCheckCalculator;
use App\Mailchimp\Campaign\Audience\BlockReasonEnum;
use App\Mailchimp\Campaign\Audience\MailchimpParallelPushService;
use App\Mailchimp\Campaign\Audience\Message\PrepareCampaignAudienceMessage;
use App\Mailchimp\Campaign\Audience\PreparationStatusEnum;
use App\Mailchimp\Campaign\Audience\PushResult;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Mailchimp\Campaign\MailchimpStaticSegmentServiceInterface;
use App\Mailchimp\Driver;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[AsMessageHandler]
class PrepareCampaignAudienceHandler
{
    private const int TOO_LARGE_THRESHOLD = 500_000;
    private const int PUSH_CONCURRENCY = 5;
    private const int PUSH_CHUNK_SIZE = 500;
    private const int TARGETED_INSERT_BATCH = 5_000;
    private const int ERROR_SUMMARY_MAX_ENTRIES = 20;

    private LoggerInterface $logger;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MailchimpStaticSegmentServiceInterface $staticSegmentService,
        private readonly MailchimpParallelPushService $parallelPushService,
        private readonly MailchimpObjectIdMapping $mailchimpObjectIdMapping,
        private readonly Driver $driver,
        private readonly AdherentRepository $adherentRepository,
        private readonly BulkInsertHelper $bulkInsertHelper,
        private readonly AudienceCheckCalculator $audienceCheckCalculator,
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

        $listId = $campaign->getMailchimpListType()
            ? $this->mailchimpObjectIdMapping->getListIdFromSource($campaign->getMailchimpListType())
            : $this->mailchimpObjectIdMapping->getMainListId();

        $campaign->markAsPreparing($message->lockedBy);
        ++$staticSegment->attempts;
        $staticSegment->builtAt = null;
        $staticSegment->buildDurationMs = null;
        $staticSegment->preparedCount = null;
        $staticSegment->erroredCount = null;
        $staticSegment->chunksTotal = null;
        $staticSegment->chunksDone = 0;
        $staticSegment->errorSummary = null;
        $this->captureFilterSnapshot($staticSegment, $campaign);
        $this->entityManager->flush();

        try {
            $emails = iterator_to_array($this->adherentRepository->findAdherentEmailsForMessage($campaign->getMessage()), preserve_keys: false);
            $expected = \count($emails);
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

            $staticSegment->chunksTotal = (int) ceil($expected / self::PUSH_CHUNK_SIZE);
            $this->entityManager->flush();

            $startedAt = microtime(true);
            $result = $this->resetAndPush($campaign, $staticSegment, $segmentId, $listId, $emails);
            $staticSegment->buildDurationMs = (int) round((microtime(true) - $startedAt) * 1000);
            $staticSegment->builtAt = new \DateTimeImmutable();
            $staticSegment->preparedCount = $result->addedCount;
            $staticSegment->refusedCount = $result->refusedCount;
            $staticSegment->erroredCount = $result->erroredCount;
            if ($result->errorMessages) {
                $staticSegment->errorSummary = implode("\n", \array_slice($result->errorMessages, 0, self::ERROR_SUMMARY_MAX_ENTRIES));
            }

            $segmentData = $this->driver->getSegment($segmentId, $listId);
            $prepared = (int) ($segmentData['member_count'] ?? 0);

            $audienceCheck = $this->audienceCheckCalculator->compute($expected, $prepared);

            $this->insertTargeted($campaign, $emails);

            $campaign->markAsReady($audienceCheck);
            $this->entityManager->flush();
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

    /**
     * @param list<string> $emails
     */
    private function resetAndPush(
        MailchimpCampaign $campaign,
        MailchimpStaticSegment $staticSegment,
        int $segmentId,
        string $listId,
        array $emails,
    ): PushResult {
        // PATCH /lists/{listId}/segments/{segmentId} replaces the segment members.
        // Empty payload guarantees we start from a clean slate before the parallel push.
        $this->staticSegmentService->update($segmentId, [], $listId);

        $result = $this->parallelPushService->pushEmails(
            segmentId: $segmentId,
            listId: $listId,
            emails: $emails,
            concurrency: self::PUSH_CONCURRENCY,
            onChunkSuccess: function () use ($staticSegment): void {
                ++$staticSegment->chunksDone;
                $this->entityManager->flush();
            },
            cancellationProbe: function () use ($campaign): bool {
                return $this->isCancellationRequested($campaign);
            },
        );

        if (!$result->isSuccess()) {
            $this->logger->warning('Parallel push completed with errors', [
                'campaign_id' => $campaign->getId(),
                'errored_chunks' => $result->erroredCount,
                'errors' => $result->errorMessages,
            ]);
        }

        return $result;
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

    /**
     * @param list<string> $emails
     */
    private function insertTargeted(MailchimpCampaign $campaign, array $emails): void
    {
        $emailToIdMap = $this->adherentRepository->mapIdsByEmails($emails);
        $messageId = $campaign->getMessage()->getId();
        $now = new \DateTimeImmutable()->format('Y-m-d H:i:s');

        $rows = [];
        foreach ($emails as $email) {
            $rows[] = [
                'message_id' => $messageId,
                'adherent_id' => $emailToIdMap[$email] ?? null,
                'targeted_at' => $now,
            ];

            if (\count($rows) >= self::TARGETED_INSERT_BATCH) {
                $this->bulkInsertHelper->insertIgnore('adherent_message_targeted', $rows);
                $rows = [];
            }
        }

        if ($rows) {
            $this->bulkInsertHelper->insertIgnore('adherent_message_targeted', $rows);
        }
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

    private function isCancellationRequested(MailchimpCampaign $campaign): bool
    {
        // Refresh from DB to pick up the flag possibly set by /prepare/cancel
        // endpoint (Phase 7) running concurrently in another worker/web request.
        $this->entityManager->refresh($campaign);

        return $campaign->isCancellationRequested();
    }
}
