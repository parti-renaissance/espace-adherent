<?php

namespace App\Form\EventListener;

use App\Entity\AbstractTranslatableEntity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class EmptyTranslationRemoverListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::SUBMIT => 'removeEmptyTranslations',
        ];
    }

    public function removeEmptyTranslations(FormEvent $event): void
    {
        /** @var AbstractTranslatableEntity $translatable */
        if (!$translatable = $event->getData()) {
            return;
        }

        $translations = $translatable->getTranslations();

        foreach ($translations as $translation) {
            if ($translation->isEmpty()) {
                $translations->removeElement($translation);
            }
        }
    }
}
