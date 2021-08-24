<?php

namespace App\Admin\Audience;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;

class AudienceAdmin extends AbstractAdmin
{
    public const SERVICE_CODE = 'app.admin.audience';

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('zones')
        ;
    }

    protected function configureFormFields(FormMapper $form)
    {
        $form->add('zones', ModelAutocompleteType::class, [
            'property' => [
                'name',
                'code',
            ],
        ]);
    }
}
