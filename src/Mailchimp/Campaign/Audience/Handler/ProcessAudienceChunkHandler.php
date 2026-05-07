<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Audience\Handler;

use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpStaticSegment;
use App\Mailchimp\Campaign\Audience\Message\FinalizeCampaignAudienceMessage;
use App\Mailchimp\Campaign\Audience\Message\ProcessAudienceChunkMessage;
use App\Mailchimp\Campaign\Audience\PreparationStatusEnum;
use App\Mailchimp\Campaign\Audience\SegmentMemberStatusEnum;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Mailchimp\Driver;
use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
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
        private readonly MailchimpStaticSegmentMemberRepository $memberRepository,
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

        $segmentId = $campaign->getStaticSegmentId();
        $staticSegment = $campaign->getMailchimpStaticSegment();
        if (null === $segmentId || null === $staticSegment) {
            $this->logger->error('[AudienceChunk] Static segment missing', ['campaign_id' => $campaign->getId()]);

            return;
        }

        $idToEmail = $this->memberRepository->findPendingEmailsByChunk($staticSegment->id, $message->chunkNumber);

        if ([] === $idToEmail) {
            // Chunk already processed (retry-safe no-op).
            return;
        }

        $listId = $this->mailchimpObjectIdMapping->getMainListId();

        [$idToStatus, $idToErrorMessage] = $this->pushChunkAndMapStatuses($segmentId, $listId, $idToEmail);

        $this->memberRepository->markRowsAsProcessed($idToStatus, $idToErrorMessage);

        $this->incrementChunksDone($staticSegment);

        if (!$this->memberRepository->existsPending($staticSegment->id)) {
            $this->bus->dispatch(new FinalizeCampaignAudienceMessage($campaign->getId()));
        }
    }

    /**
     * Push the chunk to Mailchimp and map per-row processing status + error message.
     *
     * @param array<int, string> $idToEmail row id => email
     *
     * @return array{0: array<int, SegmentMemberStatusEnum>, 1: array<int, string>}
     *                                                                              [0] row id => status, [1] row id => Mailchimp error message (only refused rows)
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

        // Mailchimp returns HTTP 400 with errors[].field = "members_to_add" when *all* emails in the
        // batch are not list subscribers. Unlike the 200 path (per-email errors), the whole request
        // is rejected. Treat as "all refused" instead of throwing — otherwise Messenger retries this
        // forever and the failure subscriber finally marks the chunk errored.
        if (400 === $statusCode) {
            $data = $response->toArray(throw: false);
            if ($this->isAllRefusedError($data)) {
                $globalMessage = $this->extractAllRefusedMessage($data);
                $ids = array_keys($idToEmail);

                return [
                    array_fill_keys($ids, SegmentMemberStatusEnum::Refused),
                    array_fill_keys($ids, $globalMessage),
                ];
            }
        }

        if (200 !== $statusCode && 204 !== $statusCode) {
            $body = $response->getContent(throw: false);
            throw new \RuntimeException(\sprintf('Mailchimp HTTP %d on chunk push: %s', $statusCode, substr($body, 0, 500)));
        }

        $data = $response->toArray(throw: false);
        $totalAdded = isset($data['total_added']) ? (int) $data['total_added'] : null;

        // Pathological case: HTTP 200 but nothing added (rejected list-side). Throw so Messenger retries
        // and the failure subscriber eventually marks the chunk as errored if retries are exhausted.
        if (null !== $totalAdded && 0 === $totalAdded && \count($emails) > 0) {
            throw new \RuntimeException(\sprintf('Mailchimp 200 but total_added=0 on chunk of %d emails.', \count($emails)));
        }

        $emailToError = $this->extractRefusedErrors($data);

        $idToStatus = [];
        $idToErrorMessage = [];
        foreach ($idToEmail as $rowId => $email) {
            if (isset($emailToError[$email])) {
                $idToStatus[$rowId] = SegmentMemberStatusEnum::Refused;
                $idToErrorMessage[$rowId] = $emailToError[$email];
            } else {
                $idToStatus[$rowId] = SegmentMemberStatusEnum::Added;
            }
        }

        return [$idToStatus, $idToErrorMessage];
    }

    private function isAllRefusedError(array $data): bool
    {
        $errors = $data['errors'] ?? null;
        if (!\is_array($errors)) {
            return false;
        }

        foreach ($errors as $error) {
            if (\is_array($error) && 'members_to_add' === ($error['field'] ?? null)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, string> email => Mailchimp error message
     */
    private function extractRefusedErrors(array $data): array
    {
        $errors = $data['errors'] ?? [];
        if (!\is_array($errors)) {
            return [];
        }

        $refused = [];
        foreach ($errors as $error) {
            if (\is_array($error) && isset($error['email_address'])) {
                $refused[(string) $error['email_address']] = (string) ($error['error'] ?? 'Mailchimp refused this email');
            }
        }

        return $refused;
    }

    private function extractAllRefusedMessage(array $data): string
    {
        $errors = $data['errors'] ?? null;
        if (\is_array($errors)) {
            foreach ($errors as $error) {
                if (\is_array($error) && 'members_to_add' === ($error['field'] ?? null) && !empty($error['message'])) {
                    return (string) $error['message'];
                }
            }
        }

        return 'None of the emails were subscribed to the list';
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
