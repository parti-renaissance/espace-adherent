<?php

namespace App\Form\AdherentMessage;

use App\Entity\AdherentMessage\Filter\ReferentElectedRepresentativeFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReferentElectedRepresentativeFilterType extends AbstractType
{
    public function getParent()
    {
        return ElectedRepresentativeFilterType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefault('data_class', ReferentElectedRepresentativeFilter::class)
        ;
    }
}
