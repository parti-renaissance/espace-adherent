<?php

namespace AppBundle\Mailchimp\Campaign\Listener;

use AppBundle\AdherentMessage\AdherentMessageTypeEnum;
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
        $request = $event->getRequest();

        switch ($event->getMessage()->getType()) {
            case AdherentMessageTypeEnum::DEPUTY:
                $request->setReplyTo('ne-pas-repondre@en-marche.fr');
                break;
            case AdherentMessageTypeEnum::CITIZEN_PROJECT:
                $request->setReplyTo('projetscitoyens@en-marche.fr');
                break;
            default:
                $request->setReplyTo('jemarche@en-marche.fr');
        }
    }
}
