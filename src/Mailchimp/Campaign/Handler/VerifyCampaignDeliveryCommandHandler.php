<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Handler;

use App\AdherentMessage\MailchimpStatusEnum;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\Audience\AudienceMessagePreparer;
use App\Mailchimp\Campaign\Command\VerifyCampaignDeliveryCommand;
use App\Mailchimp\Campaign\DeliveryDecisionEnum;
use App\Mailchimp\Campaign\PostSendDeliveryGuard;
use App\Mailchimp\Manager;
use App\Repository\MailchimpCampaignRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[AsMessageHandler]
class VerifyCampaignDeliveryCommandHandler
{
    /**
     * Confirmation window (~30 min total) polled ONCE the campaign reaches a terminal "sent", to let
     * the delivery report propagate. A zero-delivery KO is only ever declared on the final attempt of
     * THIS window — never while the campaign is still "sending" (see SENDING_* below).
     */
    private const array DELAY_SCHEDULE_MS = [
        180_000,  // ~3 min
        300_000,  // ~8 min
        300_000,  // ~13 min
        600_000,  // ~23 min
        600_000,  // ~33 min
    ];

    /**
     * Sending window: while the campaign is still "sending", poll on this dedicated cadence instead
     * of consuming the confirmation window. A large national send can stay "sending" for a long time;
     * the ceiling (~3 h) is a safety backstop against a campaign genuinely stuck in "sending".
     */
    private const int SENDING_POLL_INTERVAL_MS = 600_000; // 10 min
    private const int SENDING_MAX_POLLS = 18; // ~3 h

    private LoggerInterface $logger;

    public function __construct(
        private readonly MailchimpCampaignRepository $repository,
        private readonly Manager $manager,
        private readonly PostSendDeliveryGuard $guard,
        private readonly MessageBusInterface $bus,
        private readonly EntityManagerInterface $entityManager,
        private readonly AudienceMessagePreparer $audienceMessagePreparer,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function __invoke(VerifyCampaignDeliveryCommand $command): void
    {
        $campaign = $this->repository->find($command->campaignId);
        if (!$campaign instanceof MailchimpCampaign) {
            return;
        }

        $status = $this->manager->getCampaignStatus($campaign);
        $reportData = $this->manager->getReportData($campaign);
        // Tri-state read: an absent key means the report is not readable (404/transient), which is
        // NOT a confirmed zero. Never collapse it to 0.
        $emailsSent = \array_key_exists('emails_sent', $reportData) ? (int) $reportData['emails_sent'] : null;
        $preparedCount = $campaign->getMailchimpStaticSegment()?->preparedCount;
        $sendingWindowExhausted = $command->sendingRetry >= self::SENDING_MAX_POLLS;
        $confirmWindowExhausted = $command->countRetry >= \count(self::DELAY_SCHEDULE_MS);

        $decision = $this->guard->evaluate(
            $status ?? MailchimpStatusEnum::Save,
            $emailsSent,
            $preparedCount,
            $sendingWindowExhausted,
            $confirmWindowExhausted,
        );

        if (DeliveryDecisionEnum::Ok === $decision->kind) {
            // A recovery replica that finally delivered → mark the recovery as resolved.
            if ($campaign->isRecoveryInProgress()) {
                $campaign->markRecoverySucceeded();
                $this->entityManager->flush();
            }

            return;
        }

        if (DeliveryDecisionEnum::Pending === $decision->kind) {
            $this->reschedule($command, $status);

            return;
        }

        // Failed / Unverifiable / StillSending / NotSending → alert (logger->error → Sentry in prod).
        $this->logger->error('[Mailchimp][PostSendGuard] '.$this->describe($decision->kind), [
            'campaign_id' => $campaign->getId(),
            'external_id' => $campaign->getExternalId(),
            'message_uuid' => $campaign->getMessage()->getUuid()->toRfc4122(),
            'status' => $status?->value,
            'emails_sent' => $emailsSent,
            'prepared_count' => $preparedCount,
            'retry_count' => $command->countRetry,
            'decision' => $decision->reason,
        ]);

        // Fallback (Unit B) only on a confirmed zero delivery. Other modes (still sending, stuck,
        // unverifiable) are alert-only — replicating there would be unsafe or pointless.
        if (DeliveryDecisionEnum::Failed === $decision->kind) {
            $this->attemptRecovery($campaign);
        }
    }

    /**
     * Reschedule the next poll on the window matching the current status. While "sending", advance the
     * dedicated sending window and leave countRetry untouched, so the confirmation window only starts
     * counting once the campaign reaches "sent".
     */
    private function reschedule(VerifyCampaignDeliveryCommand $command, ?MailchimpStatusEnum $status): void
    {
        if (MailchimpStatusEnum::Sending === $status) {
            $this->bus->dispatch(
                new VerifyCampaignDeliveryCommand($command->campaignId, $command->countRetry, $command->sendingRetry + 1),
                [new DelayStamp(self::SENDING_POLL_INTERVAL_MS)],
            );

            return;
        }

        $this->bus->dispatch(
            new VerifyCampaignDeliveryCommand($command->campaignId, $command->countRetry + 1, $command->sendingRetry),
            [new DelayStamp(self::DELAY_SCHEDULE_MS[$command->countRetry])],
        );
    }

    /**
     * Zero-delivery recovery: replicate the campaign and re-inject it into the existing preparation
     * pipeline (rebuild segment → recipient_count readiness → send). Single attempt, with two
     * abort-if-original-recovered checkpoints to avoid a double send.
     */
    private function attemptRecovery(MailchimpCampaign $campaign): void
    {
        // The replica we already sent also delivered 0: stop, never replicate a second time.
        if ($campaign->isRecoveryInProgress()) {
            $campaign->markRecoveryFailed();
            $this->entityManager->flush();

            $this->logger->error('[Mailchimp][PostSendGuard] Replica also delivered 0 — recovery failed', [
                'campaign_id' => $campaign->getId(),
                'external_id' => $campaign->getExternalId(),
            ]);

            return;
        }

        // Atomic single-attempt claim: only one worker proceeds.
        if (!$this->repository->tryClaimRecovery($campaign->getId())) {
            return;
        }

        $this->entityManager->refresh($campaign);

        // Checkpoint 1 (before duplicate): the original may have delivered since detection.
        if ($this->manager->hasDelivered($campaign)) {
            $campaign->markRecoveryAborted();
            $this->entityManager->flush();

            $this->logger->warning('[Mailchimp][PostSendGuard] Original delivered before duplicate — recovery aborted', [
                'campaign_id' => $campaign->getId(),
                'external_id' => $campaign->getExternalId(),
            ]);

            return;
        }

        $replicaExternalId = $this->manager->replicateCampaign($campaign);
        if (null === $replicaExternalId) {
            $campaign->markRecoveryFailed();
            $this->entityManager->flush();

            $this->logger->error('[Mailchimp][PostSendGuard] Replicate failed — recovery aborted', [
                'campaign_id' => $campaign->getId(),
            ]);

            return;
        }

        $locker = $campaign->getPreparationLockedBy();
        if (null === $locker) {
            $campaign->markRecoveryFailed();
            $this->entityManager->flush();

            $this->logger->error('[Mailchimp][PostSendGuard] No locker available for auto-recovery — aborted', [
                'campaign_id' => $campaign->getId(),
            ]);

            return;
        }

        $originalExternalId = $campaign->getExternalId();
        $campaign->markRecoveryAttempted($replicaExternalId);
        $this->resetSegmentForRebuild($campaign);
        $this->entityManager->flush();

        // Robustness: actions/replicate is documented to copy the recipient config, but do not depend
        // on it. If the replica does not target our static segment, the pre-send guard would abort at
        // send time and strand the recovery in "attempted" forever — fail fast and explicitly here,
        // before wasting a full audience rebuild on a replica that can never go out.
        if (!$this->manager->campaignTargetsSegment($campaign)) {
            $campaign->markRecoveryFailed();
            $this->entityManager->flush();

            $this->logger->error('[Mailchimp][PostSendGuard] Replica lost its segment binding — recovery aborted', [
                'campaign_id' => $campaign->getId(),
                'original_external_id' => $originalExternalId,
                'replica_external_id' => $replicaExternalId,
            ]);

            return;
        }

        // Re-inject into the existing pipeline: rebuild audience → finalize markAsReady →
        // recipient_count readiness guard → send (Checkpoint 2 lives in Manager::sendMailchimpCampaign).
        $this->audienceMessagePreparer->prepare($campaign->getMessage(), $locker);

        $this->logger->warning('[Mailchimp][PostSendGuard] Recovery started: replica prepared and queued for send', [
            'campaign_id' => $campaign->getId(),
            'original_external_id' => $originalExternalId,
            'replica_external_id' => $replicaExternalId,
        ]);
    }

    /**
     * Force a full audience rebuild on re-preparation: clearing chunksTotal makes the prepare
     * handler take the wipe+refill path instead of resuming residual chunks from the original send.
     */
    private function resetSegmentForRebuild(MailchimpCampaign $campaign): void
    {
        $segment = $campaign->getMailchimpStaticSegment();
        if (null !== $segment) {
            $segment->chunksTotal = null;
            $segment->preparedCount = null;
        }
    }

    private function describe(DeliveryDecisionEnum $kind): string
    {
        return match ($kind) {
            DeliveryDecisionEnum::Failed => 'Zero delivery detected',
            DeliveryDecisionEnum::StillSending => 'Still sending at end of window',
            DeliveryDecisionEnum::Unverifiable => 'Delivery unverifiable',
            DeliveryDecisionEnum::NotSending => 'Campaign never started sending',
            default => 'Post-send anomaly',
        };
    }
}
