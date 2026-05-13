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
    private const int MAX_RETRIES = 6;
    private const array DELAY_SCHEDULE_MS = [
        30_000,    // 30s
        60_000,    // 1min
        300_000,   // 5min
        600_000,   // 10min
        1_800_000, // 30min
        3_600_000, // 60min
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
                'messageUuid' => $campaign->getMessage()->getUuid()->toString(),
            ]);

            return;
        }

        $campaign->incrementRetryCount();

        $isFinalAttempt = $command->countRetry >= self::MAX_RETRIES;

        if (SendDecisionEnum::Send === $decision->kind || $isFinalAttempt) {
            if (SendDecisionEnum::Send === $decision->kind) {
                $campaign->setRecipientCount($decision->recipientCount);
            } else {
                $this->logger->error('[Mailchimp] recipient_count never settled — sending anyway', [
                    'campaignId' => $command->campaignId,
                    'reason' => $decision->reason,
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

        if ($command->countRetry < self::MAX_RETRIES) {
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
                'messageUuid' => $campaign->getMessage()->getUuid()->toString(),
                'lastError' => $campaign->getDetail(),
                'retryCount' => $campaign->getRetryCount(),
            ]);
        }
    }
}
