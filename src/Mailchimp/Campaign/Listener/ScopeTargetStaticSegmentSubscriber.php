<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Listener;

use App\Mailchimp\Campaign\MailchimpStaticSegmentServiceInterface;
use App\Mailchimp\Event\CampaignEvent;
use App\Mailchimp\Events;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ScopeTargetStaticSegmentSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly MailchimpStaticSegmentServiceInterface $staticSegmentService,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::CAMPAIGN_FILTERS_PRE_BUILD => 'onCampaignFiltersPreBuild',
        ];
    }

    public function onCampaignFiltersPreBuild(CampaignEvent $event): void
    {
        $campaign = $event->getCampaign();
        $message = $campaign->getMessage();
        $filter = $message->getFilter();

        if (null === $filter) {
            return;
        }

        $scopeTargets = $filter->scopeTargets ?? [];

        if (0 === \count($scopeTargets)) {
            return;
        }

        $emails = $this->adherentRepository->getEmailsForScopeTargets($scopeTargets);

        $staticSegmentId = $campaign->getStaticSegmentId();

        $result = $this->staticSegmentService->createOrUpdate(
            \sprintf('scope_targets_%s', $message->getUuid()->toString()),
            $emails,
            $staticSegmentId
        );

        if (null === $staticSegmentId && null !== $result && false !== $result) {
            $campaign->setStaticSegmentId((int) $result);
            $this->entityManager->flush();
        }
    }
}
