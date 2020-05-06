<?php

namespace App\Admin\Filter;

use App\Entity\ReferentTag;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;

class ReferentTagAutocompleteFilter extends CallbackFilter
{
    public function getDefaultOptions()
    {
        return [
            'callback' => null,
            'field_name' => false,
            'field_type' => ModelAutocompleteType::class,
            'field_options' => [],
            'operator_options' => [],
        ];
    }

    public function getFieldOptions()
    {
        return array_merge([
            'context' => 'filter',
            'class' => ReferentTag::class,
            'req_params' => [
                'field' => 'referentTags',
            ],
            'multiple' => true,
            'property' => 'name',
            'minimum_input_length' => 1,
            'items_per_page' => 20,
        ], parent::getFieldOptions());
    }
}
