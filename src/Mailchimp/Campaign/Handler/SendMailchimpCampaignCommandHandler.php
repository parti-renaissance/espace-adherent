<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Handler;

use App\AdherentMessage\MailchimpStatusEnum;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\Command\RetrySendMailchimpCampaignCommand;
use App\Mailchimp\Campaign\Command\SendMailchimpCampaignCommand;
use App\Mailchimp\Campaign\MailchimpCampaignSendGuard;
use App\Mailchimp\Campaign\SendDecisionEnum;
use App\Mailchimp\Manager;
use App\Repository\MailchimpCampaignRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[AsMessageHandler]
class SendMailchimpCampaignCommandHandler
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly MailchimpCampaignRepository $repository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MailchimpCampaignSendGuard $sendGuard,
        private readonly Manager $manager,
        private readonly MessageBusInterface $bus,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function __invoke(SendMailchimpCampaignCommand $command): void
    {
        $campaign = $this->repository->find($command->campaignId);
        if (!$campaign instanceof MailchimpCampaign) {
            $this->logger->warning('[SendMailchimpCampaign] MailchimpCampaign not found', [
                'campaign_id' => $command->campaignId,
            ]);

            return;
        }

        $this->entityManager->refresh($campaign);

        if (\in_array($campaign->status, [MailchimpStatusEnum::Sent, MailchimpStatusEnum::Sending], true)) {
            return;
        }

        $externalId = $campaign->getExternalId();
        if (null === $externalId) {
            $this->logger->error('[SendMailchimpCampaign] Missing external id — send aborted', [
                'campaign_id' => $campaign->getId(),
            ]);

            return;
        }

        $decision = $this->sendGuard->evaluate($campaign);

        if (SendDecisionEnum::Abort === $decision->kind) {
            $campaign->markAsError($decision->reason);
            $this->entityManager->flush();

            $this->logger->error('[SendMailchimpCampaign] Send aborted by recipient guard', [
                'campaign_id' => $campaign->getId(),
                'external_id' => $externalId,
                'reason' => $decision->reason,
                'recipient_count' => $decision->recipientCount,
            ]);

            return;
        }

        if (SendDecisionEnum::Retry === $decision->kind) {
            $this->logger->warning('[SendMailchimpCampaign] Recipient count not ready, scheduling retry', [
                'campaign_id' => $campaign->getId(),
                'reason' => $decision->reason,
            ]);

            $this->bus->dispatch(
                new RetrySendMailchimpCampaignCommand($campaign->getId()),
                [new DelayStamp(30_000)]
            );

            return;
        }

        $campaign->setRecipientCount($decision->recipientCount);

        if (!$this->manager->sendMailchimpCampaign($campaign)) {
            $this->bus->dispatch(
                new RetrySendMailchimpCampaignCommand($campaign->getId()),
                [new DelayStamp(30_000)]
            );
        }
    }
}
