<?php

namespace App\Form\Renaissance;

use App\Form\AutocompleteInputType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class NewsletterZoneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('autocomplete', AutocompleteInputType::class, [
                'label' => 'Code postal',
                'attr' => [
                    'placeholder' => false,
                    'data-form' => $builder->getName(),
                ],
            ])
            ->add('postalCode', HiddenType::class)
            ->add('country', HiddenType::class)
        ;
    }
}
