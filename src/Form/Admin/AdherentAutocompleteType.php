<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Admin\AdherentAdmin;
use App\Entity\Adherent;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdherentAutocompleteType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => Adherent::class,
            'property' => 'search',
            'minimum_input_length' => 1,
            'items_per_page' => 10,
            'safe_label' => true,
            'route' => ['name' => AdherentAdmin::ADHERENT_AUTOCOMPLETE_ROUTE],
        ]);
    }

    public function getParent(): string
    {
        return ModelAutocompleteType::class;
    }
}
