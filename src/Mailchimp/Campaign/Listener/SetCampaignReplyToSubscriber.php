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

        if ($request->hasReplyTo()) {
            return;
        }

        $message = $event->getMessage();

        switch ($message->getType()) {
            case AdherentMessageTypeEnum::DEPUTY:
                $request->setReplyTo('ne-pas-repondre@en-marche.fr');
                break;
            case AdherentMessageTypeEnum::COMMITTEE:
            case AdherentMessageTypeEnum::REFERENT:
            case AdherentMessageTypeEnum::CITIZEN_PROJECT:
                $request->setReplyTo($message->getAuthor()->getEmailAddress());
                break;
            default:
                $request->setReplyTo('jemarche@en-marche.fr');
        }
    }
}
