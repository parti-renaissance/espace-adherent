<?php

namespace App\Form\Admin;

use App\Entity\Geo\Zone;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminZoneAutocompleteType extends AbstractType
{
    public function getParent()
    {
        return ModelAutocompleteType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'property' => 'name',
            'class' => Zone::class,
            'template' => 'admin/form/sonata_type_model_autocomplete.html.twig',
        ]);
    }
}
