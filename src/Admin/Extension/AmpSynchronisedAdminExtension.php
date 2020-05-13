<?php

namespace App\Admin\Extension;

use App\Form\EventListener\AmpSynchronisedListener;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Form\FormMapper;

class AmpSynchronisedAdminExtension extends AbstractAdminExtension
{
    private $ampSynchronisedListener;

    public function __construct(AmpSynchronisedListener $listener)
    {
        $this->ampSynchronisedListener = $listener;
    }

    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->getFormBuilder()
            ->addEventSubscriber($this->ampSynchronisedListener)
        ;
    }
}
