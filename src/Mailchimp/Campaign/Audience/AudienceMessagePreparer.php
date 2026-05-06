<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Audience;

use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\Audience\Message\PrepareCampaignAudienceMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class AudienceMessagePreparer
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $bus,
        private readonly SendStatusFactory $sendStatusFactory,
    ) {
    }

    public function prepare(AdherentMessage $message, Adherent $currentUser): PrepareResult
    {
        $campaign = $this->resolveCampaign($message);

        if ($this->isLockedByOther($campaign, $currentUser)) {
            return PrepareResult::conflict($this->sendStatusFactory->build($campaign));
        }

        if ($this->isAlreadyPreparedAndFresh($campaign)) {
            return PrepareResult::alreadyReady($this->sendStatusFactory->build($campaign));
        }

        $lockedBy = $currentUser->getEmailAddress();
        $campaign->markAsPreparing($lockedBy);
        $this->entityManager->flush();

        $this->bus->dispatch(new PrepareCampaignAudienceMessage($campaign->getId(), $lockedBy));

        return PrepareResult::preparing($this->sendStatusFactory->build($campaign));
    }

    public function requestCancellation(AdherentMessage $message): void
    {
        $campaign = $this->findCampaign($message);
        if (null === $campaign) {
            return;
        }

        $campaign->requestCancellation();
        $this->entityManager->flush();
    }

    private function resolveCampaign(AdherentMessage $message): MailchimpCampaign
    {
        $campaign = $this->findCampaign($message);
        if (null === $campaign) {
            throw new \LogicException(\sprintf('AdherentMessage "%s" has no MailchimpCampaign — cannot prepare audience.', $message->getUuid()->toString()));
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

        return null !== $lockedBy && $lockedBy !== $currentUser->getEmailAddress();
    }

    private function isAlreadyPreparedAndFresh(MailchimpCampaign $campaign): bool
    {
        if (PreparationStatusEnum::Ready !== $campaign->getPreparationStatus()) {
            return false;
        }

        $filter = $campaign->getMessage()->getFilter();
        $filterUpdatedAt = $filter && method_exists($filter, 'getUpdatedAt') ? $filter->getUpdatedAt() : null;
        $preparedAt = $campaign->getPreparedAt();

        return null !== $filterUpdatedAt
            && null !== $preparedAt
            && $filterUpdatedAt < $preparedAt;
    }
}
