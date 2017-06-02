<?php

namespace AppBundle\Form;

use AppBundle\Entity\ProcurationRequest;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProcurationProfileType extends AbstractProcurationType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('data_class', ProcurationRequest::class);
        $resolver->setDefault('validation_groups', ['vote', 'profile']);
    }

    public function getBlockPrefix(): string
    {
        return 'app_procuration_profile';
    }
}
