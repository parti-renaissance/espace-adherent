<?php

namespace AppBundle\Admin\Extension;

use AppBundle\Form\EventListener\EmptyTranslationRemoverListener;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Form\FormMapper;

class EmptyTranslationRemoverAdminExtension extends AbstractAdminExtension
{
    private $emptyTranslationRemoverListener;

    public function __construct(EmptyTranslationRemoverListener $listener)
    {
        $this->emptyTranslationRemoverListener = $listener;
    }

    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->getFormBuilder()
            ->addEventSubscriber($this->emptyTranslationRemoverListener)
        ;
    }
}
