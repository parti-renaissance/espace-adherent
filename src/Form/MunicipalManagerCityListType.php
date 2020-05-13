<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MunicipalManagerCityListType extends AbstractType
{
    public function getParent()
    {
        return CollectionType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'entry_type' => MunicipalManagerCityAssociationType::class,
            'entry_options' => [
               'label' => false,
            ],
        ]);
    }
}
