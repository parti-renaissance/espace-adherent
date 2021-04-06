<?php

namespace App\Form\EventListener;

use App\Entity\Adherent;
use App\Entity\Coalition\CoalitionModeratorRoleAssociation;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CoalitionModeratorRoleListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::POST_SET_DATA => 'onPostSetData',
            FormEvents::SUBMIT => 'onSubmit',
        ];
    }

    public function onPostSetData(FormEvent $event): void
    {
        $form = $event->getForm();
        /** @var Adherent $adherent */
        $adherent = $event->getData();

        if (!$adherent) {
            return;
        }

        $form->get('isCoalitionModeratorRole')->setData($adherent->isCoalitionModerator());
    }

    public function onSubmit(FormEvent $event): void
    {
        $form = $event->getForm();
        /** @var Adherent $adherent */
        $adherent = $form->getData();

        $isCoalitionModeratorRole = $form->get('isCoalitionModeratorRole')->getData();

        if ($isCoalitionModeratorRole) {
            if (!$adherent->isCoalitionModerator()) {
                $adherent->setCoalitionModeratorRole(new CoalitionModeratorRoleAssociation());
            }

            return;
        }

        $adherent->revokeCoalitionModeratorRole();
    }
}
