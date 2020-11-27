<?php

namespace App\Form;

use App\Entity\Geo\Zone;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class ZoneAutoCompleteType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'multiple' => true,
            'remote_route' => 'api_zone_autocomplete',
            'class' => Zone::class,
            'primary_key' => 'id',
            'minimum_input_length' => 2,
            'page_limit' => 25,
            'delay' => 250,
            'language' => 'fr',
            'placeholder' => 'Zones',
            'autostart' => false,
        ]);
    }

    public function getParent(): string
    {
        return Select2EntityType::class;
    }
}
