<?php

namespace AppBundle\Form\EventListener;

use AppBundle\Entity\Adherent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ManagedAreaListener implements EventSubscriberInterface
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'configureManagedAreas',
            FormEvents::SUBMIT => 'removeEmptyManagedAreas',
        ];
    }

    public function configureManagedAreas(FormEvent $event): void
    {
        /** @var Adherent $adherent */
        if (!$adherent = $event->getData()) {
            return;
        }

        if ($adherent->isEuropeanDeputy()) {
            $adherent->getEuropeanDeputyManagedArea()->setIsEuropeanDeputy(true);
        }
    }

    public function removeEmptyManagedAreas(FormEvent $event): void
    {
        /** @var Adherent $adherent */
        if (!$adherent = $event->getData()) {
            return;
        }

        $senatorManagedArea = $adherent->getSenatorManagedArea();
        if ($senatorManagedArea && !$senatorManagedArea->isValid()) {
            $adherent->revokeSenator();
            $this->em->remove($senatorManagedArea);
        }

        $europeanDeputyManagedArea = $adherent->getEuropeanDeputyManagedArea();
        if ($europeanDeputyManagedArea && !$europeanDeputyManagedArea->isValid()) {
            $adherent->revokeEuropeanDeputy();
            $this->em->remove($europeanDeputyManagedArea);
        }

        $referentManagedArea = $adherent->getReferentManagedArea();
        if ($referentManagedArea && !$referentManagedArea->isValid()) {
            $adherent->revokeReferent();
            $this->em->remove($referentManagedArea);
        }
    }
}
