<?php

namespace App\Admin\Extension;

use App\Form\EventListener\EmptyTranslationRemoverListener;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Form\FormMapper;

class EmptyTranslationRemoverAdminExtension extends AbstractAdminExtension
{
    public function configureFormFields(FormMapper $form): void
    {
        $form
            ->getFormBuilder()
            ->addEventSubscriber(new EmptyTranslationRemoverListener())
        ;
    }
}
