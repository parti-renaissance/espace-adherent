<?php

namespace AppBundle\Form\EventListener;

use AppBundle\Entity\Adherent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CommunicationManagerAreaListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::SUBMIT => 'removeEmptyCommunicationManagerArea',
        ];
    }

    public function removeEmptyCommunicationManagerArea(FormEvent $event): void
    {
        /** @var Adherent $adherent */
        if (!$adherent = $event->getData()) {
            return;
        }

        $communicationManagerArea = $adherent->getCommunicationManagerArea();

        if ($communicationManagerArea && $communicationManagerArea->getTags()->isEmpty()) {
            $adherent->revokeCommunicationManager();
        }
    }
}
