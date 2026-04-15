<?php

declare(strict_types=1);

namespace App\Admin;

use App\Form\Admin\AdminZoneAutocompleteType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Form\FormMapper;

class AdherentZoneBasedRoleAdmin extends AbstractAdmin
{
    public const SERVICE_ID = 'app.admin.adherent_zone_based_role_admin';

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'type';
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('zones', AdminZoneAutocompleteType::class, [
            'multiple' => true,
            'btn_add' => false,
        ]);
    }
}
