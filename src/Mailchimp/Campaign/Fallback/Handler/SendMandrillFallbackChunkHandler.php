<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Fallback\Handler;

use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\Fallback\MandrillCampaignPayloadBuilder;
use App\Mailchimp\Campaign\Fallback\MandrillFallbackChunkStatusEnum;
use App\Mailchimp\Campaign\Fallback\MandrillResponseParser;
use App\Mailchimp\Campaign\Fallback\MandrillSendResult;
use App\Mailchimp\Campaign\Fallback\Message\SendMandrillFallbackChunkMessage;
use App\Mailchimp\Campaign\MandrillFallbackStatusEnum;
use App\Mailer\EmailClientInterface;
use App\Mailer\Exception\MailerException;
use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
use App\Repository\AdherentMessage\MandrillFallbackChunkRepository;
use App\Repository\MailchimpCampaignRepository;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendMandrillFallbackChunkHandler
{
    private const float REJECTION_ALERT_THRESHOLD = 0.5;

    private LoggerInterface $logger;

    public function __construct(
        private readonly MailchimpCampaignRepository $campaignRepository,
        private readonly MailchimpStaticSegmentMemberRepository $memberRepository,
        private readonly MandrillFallbackChunkRepository $chunkRepository,
        private readonly MandrillCampaignPayloadBuilder $payloadBuilder,
        private readonly MandrillResponseParser $responseParser,
        private readonly EmailClientInterface $emailClient,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function __invoke(SendMandrillFallbackChunkMessage $message): void
    {
        $campaign = $this->campaignRepository->find($message->campaignId);
        if (!$campaign instanceof MailchimpCampaign) {
            return;
        }

        if (\in_array($campaign->mandrillFallbackStatus, [MandrillFallbackStatusEnum::Aborted, MandrillFallbackStatusEnum::Skipped], true)) {
            return;
        }

        if (!$this->chunkRepository->claimForSending($message->campaignId, $message->chunkNumber)) {
            $this->handleUnclaimable($message);

            return;
        }

        $segment = $campaign->getMailchimpStaticSegment();
        if (null === $segment) {
            $this->chunkRepository->markNeedsReview($message->campaignId, $message->chunkNumber);
            $this->logger->error('[Mandrill][Fallback] Static segment missing — chunk needs review', [
                'campaign_id' => $message->campaignId,
                'chunk' => $message->chunkNumber,
            ]);

            return;
        }

        $recipients = $this->memberRepository->findRecipientsForMandrillByChunk($segment->id, $message->chunkNumber);
        if ([] === $recipients) {
            // Nothing eligible in this chunk (e.g. all unsubscribed since preparation): legitimate no-op.
            $this->chunkRepository->markSent($message->campaignId, $message->chunkNumber);

            return;
        }

        $payload = $this->payloadBuilder->build($campaign->getMessage(), $message->renderedHtml, $recipients);

        try {
            $response = $this->emailClient->sendEmail(json_encode($payload, \JSON_THROW_ON_ERROR), useTemplateEndpoint: false);
        } catch (MailerException $exception) {
            // Transport failure, no Mandrill acceptance: reopen for a clean retry and let Messenger retry.
            $this->chunkRepository->markPending($message->campaignId, $message->chunkNumber);
            $this->logger->error('[Mandrill][Fallback] Chunk send transport error — reopened for retry', [
                'campaign_id' => $message->campaignId,
                'chunk' => $message->chunkNumber,
                'exception' => $exception,
            ]);

            throw $exception;
        }

        $this->reportDelivery($message, $this->responseParser->parse($response));

        $this->chunkRepository->markSent($message->campaignId, $message->chunkNumber);
    }

    /**
     * A failed claim means the chunk is no longer Pending. If already Sent it is a benign duplicate;
     * any other state (Sending) is an ambiguous retry over an in-flight send — do NOT re-send.
     */
    private function handleUnclaimable(SendMandrillFallbackChunkMessage $message): void
    {
        if (MandrillFallbackChunkStatusEnum::Sent === $this->chunkRepository->findStatus($message->campaignId, $message->chunkNumber)) {
            return;
        }

        $this->chunkRepository->markNeedsReview($message->campaignId, $message->chunkNumber);
        $this->logger->error('[Mandrill][Fallback] Ambiguous chunk retry — not re-sent, needs review', [
            'campaign_id' => $message->campaignId,
            'chunk' => $message->chunkNumber,
        ]);
    }

    private function reportDelivery(SendMandrillFallbackChunkMessage $message, MandrillSendResult $result): void
    {
        $context = [
            'campaign_id' => $message->campaignId,
            'chunk' => $message->chunkNumber,
            'sent' => $result->sent,
            'queued' => $result->queued,
            'rejected' => $result->rejected,
            'invalid' => $result->invalid,
        ];

        if ($result->total() > 0 && $result->rejectionRate() >= self::REJECTION_ALERT_THRESHOLD) {
            $this->logger->error('[Mandrill][Fallback] High rejection rate on chunk', $context);

            return;
        }

        $this->logger->info('[Mandrill][Fallback] Chunk delivered', $context);
    }
}
