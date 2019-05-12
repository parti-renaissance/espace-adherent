<?php

namespace AppBundle\Form\Admin;

use AppBundle\Admin\DistrictAdmin;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AvailableDistrictAutocompleteType extends ModelAutocompleteType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'quiet_millis' => 500,
            'placeholder' => '...',
            'property' => 'name',
            'callback' => [DistrictAdmin::class, 'prepareAutocompleteFilterCallback'],
        ]);
    }
}
