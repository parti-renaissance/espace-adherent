<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
        $builder->add('autocomplete', TextType::class, [
            'label' => false,
            'mapped' => false,
            'attr' => [
                'class' => 'address-autocomplete',
                'placeholder' => 'Adresse postale',
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
