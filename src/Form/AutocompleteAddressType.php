<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AutocompleteAddressType extends AbstractType
{
    public function getParent()
    {
        return AddressType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('autocomplete', AutocompleteInputType::class, [
            'label' => 'Adresse postale',
            'attr' => [
                'placeholder' => false,
                'data-form' => $builder->getName(),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'as_hidden' => true,
        ]);
    }
}
