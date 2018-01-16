<?php

namespace AppBundle\Admin;

use AppBundle\Form\EventListener\EmptyTranslationRemoverListener;
use Sonata\AdminBundle\Form\FormMapper;

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

    private function removeEmptyTranslationsOnSubmit(FormMapper $formMapper): void
    {
        $formMapper
            ->getFormBuilder()
            ->addEventSubscriber($this->emptyTranslationRemoverListener)
        ;
    }
}
