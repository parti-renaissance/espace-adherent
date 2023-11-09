<?php

namespace App\Mailchimp\Campaign\Listener;

use App\Entity\AdherentMessage\CorrespondentAdherentMessage;
use App\Entity\AdherentMessage\LegislativeCandidateAdherentMessage;
use App\Mailchimp\Event\RequestEvent;
use App\Mailchimp\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SetCampaignReplyToSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::CAMPAIGN_PRE_EDIT => 'preEdit',
        ];
    }

    public function preEdit(RequestEvent $event): void
    {
        if ($event->getMessage() instanceof CorrespondentAdherentMessage) {
            $event->getRequest()->setReplyTo('ne-pas-repondre@parti-renaissance.fr');

            return;
        }

        if ($event->getMessage() instanceof LegislativeCandidateAdherentMessage) {
            $event->getRequest()->setReplyTo('ne-pas-repondre@avecvous.fr');

            return;
        }

        $event->getRequest()->setReplyTo('ne-pas-repondre@parti-renaissance.fr');
    }
}
