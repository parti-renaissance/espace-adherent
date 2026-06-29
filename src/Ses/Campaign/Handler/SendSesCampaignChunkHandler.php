<?php

declare(strict_types=1);

namespace App\Ses\Campaign\Handler;

use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
use App\Repository\AdherentRepository;
use App\Repository\MailchimpCampaignRepository;
use App\Ses\Campaign\Message\SendSesCampaignChunkMessage;
use App\Ses\Campaign\Reach\CampaignReachInserter;
use App\Ses\Client\SesEmailClient;
use App\Ses\Rendering\SesMessageAssembler;
use App\Ses\Rendering\SesRecipient;
use App\Ses\Rendering\SesRecipientEmailFactory;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Sends one chunk of a campaign through SES, one recipient at a time, with per-row at-most-once.
 *
 * Each row is claimed atomically (Added -> Sending) before its SES call: a redelivery or a concurrent
 * worker can only ever pick rows still Added, so no recipient is sent twice (red-team #2). A permanent
 * rejection and a success both close the row (markRowSent) — only a transport failure reopens it
 * (Sending -> Added) and rethrows so Messenger retries the chunk without resending the rows already done.
 *
 * When the chunk leaves no sendable row in the whole segment, the campaign is completed atomically
 * (Sending -> Sent) and the reach is recorded — both idempotent, so two chunks finishing together do it once.
 */
#[AsMessageHandler]
class SendSesCampaignChunkHandler
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly MailchimpCampaignRepository $campaignRepository,
        private readonly MailchimpStaticSegmentMemberRepository $memberRepository,
        private readonly SesMessageAssembler $assembler,
        private readonly SesRecipientEmailFactory $recipientEmailFactory,
        private readonly SesEmailClient $emailClient,
        private readonly CampaignReachInserter $reachInserter,
        private readonly AdherentRepository $adherentRepository,
        private readonly EntityManagerInterface $entityManager,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function __invoke(SendSesCampaignChunkMessage $message): void
    {
        $campaign = $this->campaignRepository->find($message->campaignId);
        if (!$campaign instanceof MailchimpCampaign) {
            return;
        }

        $segment = $campaign->getMailchimpStaticSegment();
        if (null === $segment) {
            $this->logger->error('[SES][Campaign] Static segment missing — chunk skipped', [
                'campaign_id' => $message->campaignId,
                'chunk' => $message->chunkNumber,
            ]);

            return;
        }

        $recipients = $this->memberRepository->findClaimableRecipientsByChunk($segment->id, $message->chunkNumber);

        if ([] !== $recipients) {
            // Assemble the message-level HTML once per invocation; per-recipient codes are resolved below.
            $assembled = $this->assembler->assemble($campaign->getMessage());

            foreach ($recipients as $row) {
                $rowId = (int) $row['id'];

                // Claim before sending: a row already taken (Sending/Sent) is skipped, never re-sent.
                if (!$this->memberRepository->claimRowForSending($rowId)) {
                    continue;
                }

                $email = $this->recipientEmailFactory->create($assembled, new SesRecipient(
                    (string) $row['email'],
                    (string) $row['firstName'],
                    (string) $row['lastName'],
                    $row['gender'],
                    $row['publicId'],
                ));

                try {
                    $outcome = $this->emailClient->sendEmail($email);
                } catch (\Throwable $exception) {
                    // Transport failure (throttling/5xx/network), no SES acceptance: reopen for a clean
                    // retry and let Messenger retry the chunk. Rows already Sent stay Sent (no duplicate).
                    $this->memberRepository->reopenRow($rowId);
                    $this->logger->error('[SES][Campaign] Chunk row transport error — reopened for retry', [
                        'campaign_id' => $message->campaignId,
                        'chunk' => $message->chunkNumber,
                        'row' => $rowId,
                        'exception' => $exception,
                    ]);

                    throw $exception;
                }

                if (!$outcome->isSent()) {
                    // Permanent rejection (unverified/invalid address): the row is done, not retried.
                    $this->logger->warning('[SES][Campaign] Recipient permanently rejected', [
                        'campaign_id' => $message->campaignId,
                        'chunk' => $message->chunkNumber,
                        'row' => $rowId,
                        'reason' => $outcome->rejectionReason,
                    ]);

                    $this->suppressRecipientIfOnSuppressionList($outcome->rejectionReason, (string) $row['email']);
                }

                $this->memberRepository->markRowSent($rowId);
            }
        }

        $this->completeIfDone($campaign, $segment->id);
    }

    /**
     * A synchronous SES rejection because the address is on the account-level suppression list means the
     * mailbox is already known-dead: flag it locally so the next audience excludes it (this is the only
     * signal we get — a suppression-list address is never even attempted, so it produces no async SNS
     * bounce). Other permanent rejections (sender config, sandbox-unverified) are not the recipient's
     * fault, and an actually invalid mailbox surfaces as an async SNS hard bounce instead — both are left
     * as a warning only, to avoid wrongly suppressing live addresses on a systemic error.
     */
    private function suppressRecipientIfOnSuppressionList(?string $rejectionReason, string $email): void
    {
        if (null === $rejectionReason || !str_contains(strtolower($rejectionReason), 'suppression list')) {
            return;
        }

        $adherent = $this->adherentRepository->findOneByEmail($email);
        if (null === $adherent) {
            return;
        }

        $adherent->markAsEmailHardBounced();
        $this->entityManager->flush();
    }

    private function completeIfDone(MailchimpCampaign $campaign, int $staticSegmentId): void
    {
        if (0 !== $this->memberRepository->countRemainingToSend($staticSegmentId)) {
            return;
        }

        // Atomic Sending -> Sent: only the chunk that wins it records the reach, exactly once.
        if (!$this->campaignRepository->completeSending((int) $campaign->getId())) {
            return;
        }

        $this->reachInserter->insertFromSentRows($staticSegmentId, (int) $campaign->getMessage()->getId());

        $this->logger->info('[SES][Campaign] Campaign send complete', [
            'campaign_id' => $campaign->getId(),
        ]);
    }
}
