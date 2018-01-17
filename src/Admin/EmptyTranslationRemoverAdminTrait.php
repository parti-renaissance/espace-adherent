<?php

namespace AppBundle\Admin;

use AppBundle\Form\EventListener\EmptyTranslationRemoverListener;
use Symfony\Component\Form\FormBuilderInterface;

trait EmptyTranslationRemoverAdminTrait
{
    /**
     * @var EmptyTranslationRemoverListener
     */
    private $emptyTranslationRemoverListener;

    public function setEmptyTranslationRemoverListener(EmptyTranslationRemoverListener $listener): void
    {
        $this->emptyTranslationRemoverListener = $listener;
    }

    private function removeEmptyTranslationsOnSubmit(FormBuilderInterface $builder): void
    {
        $builder->addEventSubscriber($this->emptyTranslationRemoverListener);
    }
}
