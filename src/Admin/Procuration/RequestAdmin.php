<?php

namespace App\Admin\Procuration;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;

class RequestAdmin extends AbstractProcurationAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        parent::configureFormFields($form);

        $form
            ->with('Mandataire', ['class' => 'col-md-6'])
                ->add('proxy', ModelAutocompleteType::class, [
                    'label' => 'Mandataire associé',
                    'required' => false,
                    'minimum_input_length' => 2,
                    'items_per_page' => 20,
                    'property' => [
                        'search',
                    ],
                    'btn_add' => false,
                ])
            ->end()
        ;
    }
}
