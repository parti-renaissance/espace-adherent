<?php

namespace App\Form\TypeExtension;

use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberUtil;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class PhoneNumberTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'setDefaultRegion']);
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        // Bug Fix: https://github.com/misd-service-development/phone-number-bundle/pull/175
        if (PhoneNumberType::WIDGET_COUNTRY_CHOICE === $options['widget']) {
            $view->vars['block_prefixes'][] = 'misd_tel';
        }
    }

    public function getExtendedType()
    {
        return PhoneNumberType::class;
    }

    public function setDefaultRegion(FormEvent $event)
    {
        $form = $event->getForm();
        $defaultRegion = $form->getConfig()->getOption('default_region');

        if (!$event->getData() && PhoneNumberUtil::UNKNOWN_REGION !== $defaultRegion) {
            $event->setData((new PhoneNumber())->setCountryCode(33)->setNationalNumber(''));
        }
    }
}
