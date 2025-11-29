<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Geo\Zone;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminZoneAutocompleteType extends AbstractType
{
    public function getParent(): string
    {
        return ModelAutocompleteType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'property' => 'name',
            'class' => Zone::class,
            'minimum_input_length' => 1,
        ]);
    }
}
