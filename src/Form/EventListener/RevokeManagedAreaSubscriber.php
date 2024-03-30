<?php

namespace App\Form\EventListener;

use App\Entity\Adherent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class RevokeManagedAreaSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
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

        $managedArea = $adherent->getManagedArea();
        $assessorManagedArea = $adherent->getAssessorManagedArea();
        $jecouteManagedArea = $adherent->getJecouteManagedArea();

        if ($managedArea && $managedArea->getTags()->isEmpty()) {
            $adherent->revokeReferent();
        }

        if ($assessorManagedArea && empty($assessorManagedArea->getCodes())) {
            $adherent->revokeAssessorManager();
        }

        if ($jecouteManagedArea && null === $jecouteManagedArea->getZone()) {
            $adherent->revokeJecouteManager();
        }
    }
}
