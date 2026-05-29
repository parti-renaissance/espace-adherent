<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Handler;

use App\AdherentMessage\MailchimpStatusEnum;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\Command\RetrySendMailchimpCampaignCommand;
use App\Mailchimp\Campaign\MailchimpCampaignSendGuard;
use App\Mailchimp\Campaign\SendDecisionEnum;
use App\Mailchimp\Manager;
use App\Repository\MailchimpCampaignRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[AsMessageHandler]
final class RetrySendMailchimpCampaignCommandHandler
{
    private const array DELAY_SCHEDULE_MS = [
        30_000,    // r1 ~1:00
        60_000,    // r2 ~2:00
        90_000,    // r3 ~3:30
        120_000,   // r4 ~5:30
        180_000,   // r5 ~8:30
        240_000,   // r6 ~12:30
        300_000,   // r7 ~17:30
        600_000,   // r8 ~27:30
        1_200_000, // r9 ~47:30
        1_800_000, // r10 ~1:17
        3_600_000, // r11 ~1:17
    ];

    public function __construct(
        private readonly MailchimpCampaignRepository $repository,
        private readonly Manager $manager,
        private readonly EntityManagerInterface $entityManager,
        private readonly MailchimpCampaignSendGuard $sendGuard,
        private readonly MessageBusInterface $bus,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(RetrySendMailchimpCampaignCommand $command): void
    {
        $campaign = $this->repository->find($command->campaignId);

        if (!$campaign instanceof MailchimpCampaign) {
            return;
        }

        $this->entityManager->refresh($campaign);

        if (\in_array($campaign->status, [MailchimpStatusEnum::Sent, MailchimpStatusEnum::Sending], true)) {
            return;
        }

        $decision = $this->sendGuard->evaluate($campaign);

        if (SendDecisionEnum::Abort === $decision->kind) {
            $campaign->markAsError($decision->reason);
            $campaign->addRetryAttempt(false, $decision->reason);

            $this->entityManager->flush();

            $this->logger->error('[Mailchimp] Campaign send aborted by recipient guard', [
                'campaignId' => $command->campaignId,
                'externalId' => $campaign->getExternalId(),
                'reason' => $decision->reason,
                'recipientCount' => $decision->recipientCount,
                'messageUuid' => $campaign->getMessage()->getUuid()->toRfc4122(),
            ]);

            return;
        }

        $campaign->incrementRetryCount();

        $isFinalAttempt = $command->countRetry >= \count(self::DELAY_SCHEDULE_MS);

        // On exhaustion, a non-Send decision may only go out if it is explicitly force-sendable (a
        // readable undershoot = a subset of legitimate recipients). An unreadable count is NOT
        // force-sendable: blind-sending could reach a polluted/wrong audience → abort and alert.
        if ($isFinalAttempt
            && SendDecisionEnum::Send !== $decision->kind
            && !$decision->forceSendOnExhaustion
        ) {
            $campaign->markAsError($decision->reason);
            $campaign->addRetryAttempt(false, $decision->reason);

            $this->entityManager->flush();

            $this->logger->error('[Mailchimp] Send aborted after retry exhaustion — recipient_count never confirmed', [
                'campaignId' => $command->campaignId,
                'externalId' => $campaign->getExternalId(),
                'reason' => $decision->reason,
                'messageUuid' => $campaign->getMessage()->getUuid()->toRfc4122(),
            ]);

            return;
        }

        if (SendDecisionEnum::Send === $decision->kind || $isFinalAttempt) {
            if (SendDecisionEnum::Send === $decision->kind) {
                $campaign->setRecipientCount($decision->recipientCount);
            } else {
                // Final force-send of a readable undershoot: persist the count we are sending to so
                // the DB reflects the real audience instead of a stale/null value.
                if (null !== $decision->recipientCount) {
                    $campaign->setRecipientCount($decision->recipientCount);
                }

                $this->logger->error('[Mailchimp] recipient_count never settled — sending anyway', [
                    'campaignId' => $command->campaignId,
                    'reason' => $decision->reason,
                    'recipientCount' => $decision->recipientCount,
                ]);
            }

            $success = $this->manager->retrySendCampaign($campaign);
        } else {
            $success = false;
        }

        $campaign->addRetryAttempt($success, $success ? null : ($decision->reason ?? $campaign->getDetail()));

        $this->entityManager->flush();

        if ($success) {
            $this->logger->info('[Mailchimp] Campaign retry succeeded', [
                'campaignId' => $command->campaignId,
                'retryCount' => $campaign->getRetryCount(),
            ]);

            return;
        }

        if ($command->countRetry < \count(self::DELAY_SCHEDULE_MS)) {
            $delay = self::DELAY_SCHEDULE_MS[$command->countRetry];

            $this->bus->dispatch(
                new RetrySendMailchimpCampaignCommand($command->campaignId, $command->countRetry + 1),
                [new DelayStamp($delay)]
            );

            $this->logger->warning('[Mailchimp] Campaign retry scheduled', [
                'campaignId' => $command->campaignId,
                'retryCount' => $command->countRetry + 1,
                'delayMs' => $delay,
            ]);
        } else {
            $this->logger->error('[Mailchimp] Campaign retry exhausted', [
                'campaignId' => $command->campaignId,
                'externalId' => $campaign->getExternalId(),
                'staticSegmentId' => $campaign->getStaticSegmentId(),
                'messageUuid' => $campaign->getMessage()->getUuid()->toRfc4122(),
                'lastError' => $campaign->getDetail(),
                'retryCount' => $campaign->getRetryCount(),
            ]);
        }
    }
}
