<?php

namespace App\Mailchimp\Campaign\Listener;

use App\Mailchimp\CampaignEvent;
use App\Mailchimp\Events;
use App\Mailchimp\Synchronisation\MailchimpSegmentHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SynchroniseCampaignSegmentsSubscriber implements EventSubscriberInterface
{
    private $handler;

    public function __construct(MailchimpSegmentHandler $handler)
    {
        $this->handler = $handler;
    }

    public static function getSubscribedEvents()
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
                $this->handler->synchronize($mailchimpSegment);
            }
        }
    }
}
