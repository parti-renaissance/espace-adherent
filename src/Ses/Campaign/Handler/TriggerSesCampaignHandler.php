<?php

declare(strict_types=1);

namespace App\Ses\Campaign\Handler;

use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
use App\Repository\MailchimpCampaignRepository;
use App\Ses\Campaign\Message\SendSesCampaignChunkMessage;
use App\Ses\Campaign\Message\TriggerSesCampaignMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Orchestrates a SES campaign send: claims the campaign into Sending, then fans out one
 * SendSesCampaignChunkMessage per staged chunk that still has sendable rows.
 *
 * Unlike the Mandrill fallback it ports from, there is no Mailchimp I/O (no delivery checkpoint, no
 * rendered-HTML fetch — the HTML is assembled app-side) and no per-chunk ledger to seed: the
 * at-most-once guarantee lives in each row's processing_status, claimed individually by the worker.
 *
 * Idempotent: the atomic claim makes a redelivery a no-op (campaign already Sending/Sent), and even a
 * duplicated chunk dispatch is harmless since the worker claims rows one by one.
 */
#[AsMessageHandler]
class TriggerSesCampaignHandler
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly MailchimpCampaignRepository $campaignRepository,
        private readonly MailchimpStaticSegmentMemberRepository $memberRepository,
        private readonly MessageBusInterface $bus,
        private readonly EntityManagerInterface $entityManager,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function __invoke(TriggerSesCampaignMessage $message): void
    {
        $campaign = $this->campaignRepository->find($message->campaignId);
        if (!$campaign instanceof MailchimpCampaign) {
            return;
        }

        // Atomic claim: a redelivered trigger (campaign already Sending/Sent) returns here, never fanning out twice.
        if (!$this->campaignRepository->claimForSending($message->campaignId)) {
            return;
        }

        $segment = $campaign->getMailchimpStaticSegment();
        if (null === $segment) {
            $this->fail($campaign, 'No prepared segment', ['campaign_id' => $message->campaignId]);

            return;
        }

        if (0 === $this->memberRepository->countRemainingToSend($segment->id)) {
            $this->fail($campaign, 'No sendable recipient', ['campaign_id' => $message->campaignId]);

            return;
        }

        $chunkNumbers = $this->memberRepository->findChunkNumbersToSend($segment->id);
        foreach ($chunkNumbers as $chunkNumber) {
            $this->bus->dispatch(new SendSesCampaignChunkMessage($message->campaignId, $chunkNumber));
        }

        $this->logger->info('[SES][Campaign] Fan-out dispatched', [
            'campaign_id' => $message->campaignId,
            'chunks' => \count($chunkNumbers),
        ]);
    }

    private function fail(MailchimpCampaign $campaign, string $reason, array $context): void
    {
        $campaign->markAsError($reason);
        $this->entityManager->flush();

        $this->logger->error(\sprintf('[SES][Campaign] %s — send failed', $reason), $context);
    }
}
