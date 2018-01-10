<?php

namespace AppBundle\Form\EventListener;

use AppBundle\Entity\EntityTranslatableTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class EmptyTranslationRemoverListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::SUBMIT => 'onSubmit',
        ];
    }

    public function onSubmit(FormEvent $event): void
    {
        /* @var $translatable EntityTranslatableTrait */
        if (!$translatable = $event->getData()) {
            return;
        }

        $translatable->removeEmptyTranslations();
    }
}
