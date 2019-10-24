<?php

namespace AppBundle\Mailchimp\Campaign\Listener;

use AppBundle\Mailchimp\Events;
use AppBundle\Mailchimp\RequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SetCampaignReplyToSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            Events::CAMPAIGN_PRE_EDIT => 'preEdit',
        ];
    }

    public function preEdit(RequestEvent $event): void
    {
        $event->getRequest()->setReplyTo('ne-pas-repondre@en-marche.fr');
    }
}
