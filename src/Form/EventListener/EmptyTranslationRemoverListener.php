<?php

namespace AppBundle\Form\EventListener;

use AppBundle\Entity\AbstractTranslatableEntity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class EmptyTranslationRemoverListener implements EventSubscriberInterface
{
    private $optionalLocales;

    public function __construct(array $locales, string $defaultLocale)
    {
        $this->optionalLocales = array_diff_key($locales, [$defaultLocale]);
    }

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

        $translatable->removeEmptyTranslations($this->optionalLocales);
    }
}
