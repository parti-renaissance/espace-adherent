<?php

namespace App\Admin;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;

class CommitteeMembershipAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('committee', ModelAutocompleteType::class, [
            'label' => false,
            'property' => 'name',
            'required' => true,
            'minimum_input_length' => 1,
            'items_per_page' => 50,
        ]);
    }
}
