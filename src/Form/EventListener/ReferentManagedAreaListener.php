<?php

namespace AppBundle\Form\EventListener;

use AppBundle\Entity\Adherent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ReferentManagedAreaListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::SUBMIT => 'removeEmptyManagedArea',
        ];
    }

    public function removeEmptyManagedArea(FormEvent $event): void
    {
        /** @var Adherent $adherent */
        if (!$adherent = $event->getData()) {
            return;
        }

        $managedArea = $adherent->getAdherentReferentData();

        if ($managedArea && $managedArea->getTags()->isEmpty()) {
            $adherent->revokeReferent();
        }
    }
}
