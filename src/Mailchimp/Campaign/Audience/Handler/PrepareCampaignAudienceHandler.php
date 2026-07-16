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
use App\Mailchimp\Campaign\Audience\Message\ProcessAudienceChunkMessage;
use App\Mailchimp\Campaign\Audience\PreparationStatusEnum;
use App\Mailchimp\Campaign\Audience\SegmentMemberStatusEnum;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Mailchimp\Campaign\MailchimpStaticSegmentServiceInterface;
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
    private const int SES_PUSH_CHUNK_SIZE = 50;
    private const int MC_PUSH_CHUNK_SIZE = 500;
    private const int MEMBER_INSERT_BATCH = 5_000;

    private LoggerInterface $logger;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdherentRepository $adherentRepository,
        private readonly MailchimpStaticSegmentMemberRepository $memberRepository,
        private readonly BulkInsertHelper $bulkInsertHelper,
        private readonly MessageBusInterface $bus,
        private readonly NormalizerInterface $normalizer,
        private readonly MailchimpStaticSegmentServiceInterface $staticSegmentService,
        private readonly MailchimpObjectIdMapping $mailchimpObjectIdMapping,
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

        $sendViaMailchimp = $campaign->sendViaMailchimp;

        // Skip when the segment is Ready and nobody asked for a (re-)send: the message is most
        // likely a residual Messenger redelivery. A pending-send forces the rebuild path even on
        // Ready state (e.g. a Ready segment that must be rebuilt after the user re-clicks Send).
        if (!$campaign->isPendingSend()
            && PreparationStatusEnum::Ready === $campaign->getPreparationStatus()
        ) {
            return;
        }

        $staticSegment = $campaign->getMailchimpStaticSegment();
        $segmentId = $campaign->getStaticSegmentId();
        // Mailchimp fallback needs the remote segment id (provisioned at prepare-time); SES only
        // needs the local segment entity.
        if (null === $staticSegment || ($sendViaMailchimp && null === $segmentId)) {
            $this->logger->error('Static segment not initialised before /prepare', ['campaign_id' => $campaign->getId()]);
            $this->failPreparation($campaign, BlockReasonEnum::MailchimpUnavailable);
            $this->entityManager->flush();

            return;
        }

        if (PreparationStatusEnum::Preparing === $campaign->getPreparationStatus()
            && null !== $staticSegment->chunksTotal
            && $staticSegment->chunksTotal > 0
        ) {
            if ($sendViaMailchimp) {
                $this->redispatchPendingChunks($campaign);
            } else {
                $this->redispatchFinalize($campaign);
            }

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
        $this->captureFilterSnapshot($staticSegment, $campaign);
        $this->entityManager->flush();

        try {
            // Wipe any residual rows from a previous preparation before bulk-inserting.
            $this->memberRepository->deleteBySegmentId($staticSegment->id);

            $expected = $this->loadAndInsertMembers($campaign, $sendViaMailchimp);
            $staticSegment->expectedCount = $expected;
            $this->entityManager->flush();

            if (0 === $expected) {
                $this->failPreparation($campaign, BlockReasonEnum::Empty);
                $staticSegment->errorSummary = 'SQL audience returned 0 valid emails.';
                $this->entityManager->flush();

                return;
            }

            $chunksTotal = (int) ceil($expected / $this->pushChunkSize($sendViaMailchimp));
            $staticSegment->chunksTotal = $chunksTotal;

            $this->entityManager->flush();

            $campaignId = $campaign->getId();

            if ($sendViaMailchimp) {
                if (!$this->staticSegmentService->update((int) $segmentId, [], $this->mailchimpObjectIdMapping->getMainListId())) {
                    throw new \RuntimeException(\sprintf('Failed to wipe Mailchimp static segment %d before refill (campaign %d).', $segmentId, $campaignId));
                }

                for ($n = 1; $n <= $chunksTotal; ++$n) {
                    $this->bus->dispatch(new ProcessAudienceChunkMessage($campaignId, $n));
                }
            }

            $this->bus->dispatch(new FinalizeCampaignAudienceMessage($campaignId));
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

    private function redispatchPendingChunks(MailchimpCampaign $campaign): void
    {
        $staticSegmentId = $campaign->getMailchimpStaticSegment()->id;
        $pendingChunks = $this->memberRepository->findChunksWithPending($staticSegmentId);
        $campaignId = $campaign->getId();

        foreach ($pendingChunks as $chunkNumber) {
            $this->bus->dispatch(new ProcessAudienceChunkMessage($campaignId, $chunkNumber));
        }

        $this->bus->dispatch(new FinalizeCampaignAudienceMessage($campaignId));
    }

    private function redispatchFinalize(MailchimpCampaign $campaign): void
    {
        $this->bus->dispatch(new FinalizeCampaignAudienceMessage($campaign->getId()));
    }

    private function loadAndInsertMembers(MailchimpCampaign $campaign, bool $sendViaMailchimp): int
    {
        $staticSegmentId = $campaign->getMailchimpStaticSegment()->id;
        $now = new \DateTimeImmutable()->format('Y-m-d H:i:s');
        $adherentIds = $this->adherentRepository->findAdherentIdsForMessage($campaign->getMessage());

        foreach (array_chunk($adherentIds, self::MEMBER_INSERT_BATCH, true) as $batch) {
            $this->insertMemberBatch($staticSegmentId, $batch, $now, $sendViaMailchimp);
        }

        return \count($adherentIds);
    }

    /**
     * @param array<int, int> $batch index (= absolute row position) => adherent id
     */
    private function insertMemberBatch(int $staticSegmentId, array $batch, string $now, bool $sendViaMailchimp): void
    {
        $status = $sendViaMailchimp ? SegmentMemberStatusEnum::Pending : SegmentMemberStatusEnum::Added;
        $chunkSize = $this->pushChunkSize($sendViaMailchimp);

        $rows = [];
        foreach ($batch as $index => $adherentId) {
            $rows[] = [
                'static_segment_id' => $staticSegmentId,
                'adherent_id' => $adherentId,
                'chunk_number' => intdiv($index, $chunkSize) + 1,
                'processing_status' => $status->value,
                'created_at' => $now,
            ];
        }

        $this->bulkInsertHelper->insertIgnore('mailchimp_static_segment_member', $rows);
    }

    private function pushChunkSize(bool $sendViaMailchimp): int
    {
        return $sendViaMailchimp ? self::MC_PUSH_CHUNK_SIZE : self::SES_PUSH_CHUNK_SIZE;
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
