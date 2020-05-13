<?php

namespace App\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class BoardMemberListener implements EventSubscriberInterface
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
        $adherent = $event->getData();

        if (!$adherent || !$boardMember = $adherent->getBoardMember()) {
            return;
        }

        $form->get('boardMemberArea')->setData($boardMember->getArea());
        $form->get('boardMemberRoles')->setData($boardMember->getRoles());
    }

    public function onSubmit(FormEvent $event): void
    {
        $form = $event->getForm();
        $adherent = $form->getData();

        $area = $form->get('boardMemberArea')->getData();
        $roles = $form->get('boardMemberRoles')->getData();

        if (!$area || 0 === $roles->count()) {
            $adherent->revokeBoardMember();

            return;
        }

        $adherent->setBoardMember($area, $roles);
    }
}
