<?php

namespace AppBundle\Form\TypeExtension;

use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class PhoneNumberTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'setDefaultRegion']);
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
