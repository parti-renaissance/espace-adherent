<?php

namespace App\Admin\Extension;

use App\Form\EventListener\EmptyTranslationRemoverListener;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Form\FormMapper;

class EmptyTranslationRemoverAdminExtension extends AbstractAdminExtension
{
    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->getFormBuilder()
            ->addEventSubscriber(new EmptyTranslationRemoverListener())
        ;
    }
}
