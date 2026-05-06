<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Audience\Handler;

use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpStaticSegment;
use App\Mailchimp\Campaign\Audience\Message\FinalizeCampaignAudienceMessage;
use App\Mailchimp\Campaign\Audience\Message\ProcessAudienceChunkMessage;
use App\Mailchimp\Campaign\Audience\PreparationStatusEnum;
use App\Mailchimp\Campaign\Audience\TargetedProcessingStatusEnum;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Mailchimp\Driver;
use App\Repository\AdherentMessage\AdherentMessageTargetedRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class ProcessAudienceChunkHandler
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdherentMessageTargetedRepository $targetedRepository,
        private readonly Driver $driver,
        private readonly MailchimpObjectIdMapping $mailchimpObjectIdMapping,
        private readonly MessageBusInterface $bus,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function __invoke(ProcessAudienceChunkMessage $message): void
    {
        $campaign = $this->entityManager->find(MailchimpCampaign::class, $message->mailchimpCampaignId);
        if (!$campaign) {
            $this->logger->warning('[AudienceChunk] MailchimpCampaign not found', [
                'campaign_id' => $message->mailchimpCampaignId,
                'chunk' => $message->chunkNumber,
            ]);

            return;
        }

        $this->entityManager->refresh($campaign);

        if (PreparationStatusEnum::Preparing !== $campaign->getPreparationStatus()) {
            return;
        }

        if ($campaign->isCancellationRequested()) {
            return;
        }

        $segmentId = $campaign->getStaticSegmentId();
        $staticSegment = $campaign->getMailchimpStaticSegment();
        if (null === $segmentId || null === $staticSegment) {
            $this->logger->error('[AudienceChunk] Static segment missing', ['campaign_id' => $campaign->getId()]);

            return;
        }

        $messageId = $campaign->getMessage()->getId();
        $idToEmail = $this->targetedRepository->findPendingEmailsByChunk($messageId, $message->chunkNumber);

        if ([] === $idToEmail) {
            // Chunk already processed (retry-safe no-op).
            return;
        }

        $listId = $this->mailchimpObjectIdMapping->getMainListId();

        $idToStatus = $this->pushChunkAndMapStatuses($segmentId, $listId, $idToEmail);

        $this->targetedRepository->markRowsAsProcessed($idToStatus);

        $this->incrementChunksDone($staticSegment);

        if (!$this->targetedRepository->existsPending($messageId)) {
            $this->bus->dispatch(new FinalizeCampaignAudienceMessage($campaign->getId()));
        }
    }

    /**
     * Push the chunk to Mailchimp and map per-row processing status.
     *
     * @param array<int, string> $idToEmail row id => email
     *
     * @return array<int, TargetedProcessingStatusEnum> row id => status
     */
    private function pushChunkAndMapStatuses(int $segmentId, string $listId, array $idToEmail): array
    {
        $emails = array_values($idToEmail);
        $uri = \sprintf('/lists/%s/segments/%d', $listId, $segmentId);

        $response = $this->driver->send('POST', $uri, ['members_to_add' => $emails], blockOnResponseLog: false);
        if (null === $response) {
            throw new \RuntimeException('Mailchimp transport error on chunk push (no response).');
        }

        $statusCode = $response->getStatusCode();
        if (200 !== $statusCode && 204 !== $statusCode) {
            $body = (string) $response->getContent(throw: false);
            throw new \RuntimeException(\sprintf('Mailchimp HTTP %d on chunk push: %s', $statusCode, substr($body, 0, 500)));
        }

        $data = $response->toArray(throw: false);
        $totalAdded = isset($data['total_added']) ? (int) $data['total_added'] : null;

        // Pathological case: HTTP 200 but nothing added (rejected list-side). Throw so Messenger retries
        // and the failure subscriber eventually marks the chunk as errored if retries are exhausted.
        if (null !== $totalAdded && 0 === $totalAdded && \count($emails) > 0) {
            throw new \RuntimeException(\sprintf('Mailchimp 200 but total_added=0 on chunk of %d emails.', \count($emails)));
        }

        $refused = $this->extractRefusedEmails($data);
        $refusedSet = array_flip($refused);

        $idToStatus = [];
        foreach ($idToEmail as $rowId => $email) {
            $idToStatus[$rowId] = isset($refusedSet[$email])
                ? TargetedProcessingStatusEnum::Refused
                : TargetedProcessingStatusEnum::Added;
        }

        return $idToStatus;
    }

    /**
     * @return list<string>
     */
    private function extractRefusedEmails(array $data): array
    {
        $errors = $data['errors'] ?? [];
        if (!\is_array($errors)) {
            return [];
        }

        $refused = [];
        foreach ($errors as $error) {
            if (\is_array($error) && isset($error['email_address'])) {
                $refused[] = (string) $error['email_address'];
            }
        }

        return $refused;
    }

    private function incrementChunksDone(MailchimpStaticSegment $staticSegment): void
    {
        $this->entityManager->createQuery(
            'UPDATE '.MailchimpStaticSegment::class.' s SET s.chunksDone = s.chunksDone + 1 WHERE s.id = :id'
        )
            ->setParameter('id', $staticSegment->id)
            ->execute()
        ;
    }
}
