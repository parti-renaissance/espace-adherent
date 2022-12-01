<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class AutocompleteAddressType extends AbstractType
{
    public function getParent(): ?string
    {
        return AddressType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('address', AutocompleteInputType::class);
    }
}
