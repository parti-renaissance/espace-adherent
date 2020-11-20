<?php

namespace App\Adherent\Certification\Listener;

use App\Adherent\Certification\Events;
use App\Mailchimp\Synchronisation\Command\AdherentChangeCommand;
use App\Membership\AdherentEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class UpdateMailchimpContactListener implements EventSubscriberInterface
{
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::ADHERENT_CERTIFIED => 'onAdherentCertifiedChange',
            Events::ADHERENT_UNCERTIFIED => 'onAdherentCertifiedChange',
        ];
    }

    public function onAdherentCertifiedChange(AdherentEvent $event): void
    {
        $adherent = $event->getAdherent();

        $this->bus->dispatch(new AdherentChangeCommand($adherent->getUuid(), $adherent->getEmailAddress()));
    }
}
