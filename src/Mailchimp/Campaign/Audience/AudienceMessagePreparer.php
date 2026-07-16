<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Audience;

use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\Audience\Message\PrepareCampaignAudienceMessage;
use App\Mailchimp\Campaign\MailchimpChannelInitializer;
use App\Mailchimp\Campaign\StaticSegmentInitializer;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\MessageBusInterface;

class AudienceMessagePreparer
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $bus,
        private readonly SendStatusFactory $sendStatusFactory,
        private readonly StaticSegmentInitializer $staticSegmentInitializer,
        private readonly MailchimpChannelInitializer $mailchimpChannelInitializer,
        private readonly AdherentRepository $adherentRepository,
        private readonly int $sendViaMailchimpThreshold,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function prepare(AdherentMessage $message, Adherent $currentUser): PrepareResult
    {
        $campaign = $this->resolveCampaign($message);

        if ($this->isLockedByOther($campaign, $currentUser)) {
            return PrepareResult::conflict($this->sendStatusFactory->build($campaign));
        }

        $recipientCount = $this->adherentRepository->countAdherentsForMessage($message, byEmail: true);
        $campaign->sendViaMailchimp = $recipientCount > $this->sendViaMailchimpThreshold;

        if ($campaign->sendViaMailchimp) {
            $this->logger->warning('[Publication] Mailchimp send channel selected (recipients over threshold)', [
                'campaign_id' => $campaign->getId(),
                'recipient_count' => $recipientCount,
                'threshold' => $this->sendViaMailchimpThreshold,
            ]);
            $this->mailchimpChannelInitializer->ensureRemoteChannel($campaign);
        } else {
            $this->staticSegmentInitializer->ensureLocalSegment($campaign);
        }

        $campaign->markAsPreparing($currentUser);
        $campaign->markAsPendingSend();
        $campaign->getMailchimpStaticSegment()->startNewRun();
        $this->entityManager->flush();

        try {
            $this->bus->dispatch(new PrepareCampaignAudienceMessage($campaign->getId(), $currentUser->getId()));
        } catch (\Throwable $e) {
            $this->logger->error('[AudienceMessagePreparer] PrepareCampaignAudienceMessage dispatch failed', [
                'campaign_id' => $campaign->getId(),
                'exception' => $e,
            ]);

            throw $e;
        }

        return PrepareResult::preparing($this->sendStatusFactory->build($campaign));
    }

    private function resolveCampaign(AdherentMessage $message): MailchimpCampaign
    {
        $campaign = $this->findCampaign($message);
        if (null === $campaign) {
            throw new \LogicException(\sprintf('AdherentMessage "%s" has no MailchimpCampaign — cannot prepare audience.', $message->getUuid()->toRfc4122()));
        }

        return $campaign;
    }

    private function findCampaign(AdherentMessage $message): ?MailchimpCampaign
    {
        // Since 2025-01-01 only one MailchimpCampaign exists per message; legacy
        // multi-campaign records are read but only the first is acted on.
        return $message->getMailchimpCampaigns()[0] ?? null;
    }

    private function isLockedByOther(MailchimpCampaign $campaign, Adherent $currentUser): bool
    {
        if (PreparationStatusEnum::Preparing !== $campaign->getPreparationStatus()) {
            return false;
        }

        $lockedBy = $campaign->getPreparationLockedBy();

        return null !== $lockedBy && $lockedBy->getId() !== $currentUser->getId();
    }
}
