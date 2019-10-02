<?php

namespace AppBundle\Form\EventListener;

use AppBundle\Entity\Adherent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class RevokeManagedAreaSubscriber implements EventSubscriberInterface
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

        $managedArea = $adherent->getManagedArea();
        $assessorManagedArea = $adherent->getAssessorManagedArea();
        $procurationManagedArea = $adherent->getProcurationManagedArea();
        $jecouteManagedArea = $adherent->getJecouteManagedArea();

        if ($managedArea && $managedArea->getTags()->isEmpty()) {
            $adherent->revokeReferent();
        }

        if ($assessorManagedArea && empty($assessorManagedArea->getCodes())) {
            $adherent->revokeAssessorManager();
        }

        if ($procurationManagedArea && empty($procurationManagedArea->getCodes())) {
            $adherent->revokeProcurationManager();
        }

        if ($jecouteManagedArea && empty($jecouteManagedArea->getCodes())) {
            $adherent->revokeJecouteManager();
        }
    }
}
