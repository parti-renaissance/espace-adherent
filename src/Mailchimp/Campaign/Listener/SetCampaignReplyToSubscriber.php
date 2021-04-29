<?php

namespace App\Mailchimp\Campaign\Listener;

use App\Entity\AdherentMessage\CoalitionAdherentMessageInterface;
use App\Mailchimp\Event\RequestEvent;
use App\Mailchimp\Events;
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
        if ($event->getMessage() instanceof CoalitionAdherentMessageInterface) {
            $event->getRequest()->setReplyTo('contact@pourunecause.fr');

            return;
        }

        $event->getRequest()->setReplyTo('ne-pas-repondre@en-marche.fr');
    }
}
