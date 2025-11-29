<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Listener;

use App\Mailchimp\Event\CampaignEvent;
use App\Mailchimp\Events;
use App\Mailchimp\Manager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SynchroniseCampaignSegmentsSubscriber implements EventSubscriberInterface
{
    private $manager;
    private $entityManager;

    public function __construct(Manager $manager, EntityManagerInterface $entityManager)
    {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::CAMPAIGN_FILTERS_PRE_BUILD => 'preBuild',
        ];
    }

    public function preBuild(CampaignEvent $event): void
    {
        $campaign = $event->getCampaign();

        foreach ($campaign->getMailchimpSegments() as $mailchimpSegment) {
            if (!$mailchimpSegment->getExternalId()) {
                if ($segmentId = $this->manager->createMailchimpSegment($mailchimpSegment)) {
                    $mailchimpSegment->setExternalId($segmentId);

                    $this->entityManager->flush();
                }
            }
        }
    }
}
