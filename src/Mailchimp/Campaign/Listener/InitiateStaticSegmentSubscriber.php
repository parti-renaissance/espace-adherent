<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Listener;

use App\Entity\AdherentMessage\MailchimpStaticSegment;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Mailchimp\Campaign\MailchimpStaticSegmentServiceInterface;
use App\Mailchimp\Event\CampaignEvent;
use App\Mailchimp\Events;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InitiateStaticSegmentSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MailchimpStaticSegmentServiceInterface $staticSegmentService,
        private readonly MailchimpObjectIdMapping $mailchimpObjectIdMapping,
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

        if (null !== $campaign->getStaticSegmentId()) {
            return;
        }

        $listId = $campaign->getMailchimpListType()
            ? $this->mailchimpObjectIdMapping->getListIdFromSource($campaign->getMailchimpListType())
            : $this->mailchimpObjectIdMapping->getMainListId();

        $segmentName = \sprintf('PROD_%s', $campaign->getMessage()->getUuid()->toRfc4122());

        $segmentId = $this->staticSegmentService->create($segmentName, [], $listId);

        if (null === $segmentId) {
            return;
        }

        $campaign->setStaticSegmentId($segmentId);

        $segment = new MailchimpStaticSegment($campaign);
        $segment->mailchimpSegmentId = $segmentId;
        $segment->name = $segmentName;
        $campaign->setMailchimpStaticSegment($segment);
        $this->entityManager->persist($segment);

        $this->entityManager->flush();
    }
}
