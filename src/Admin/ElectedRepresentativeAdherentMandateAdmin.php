<?php

declare(strict_types=1);

namespace App\Admin;

use App\Form\Admin\AdminZoneAutocompleteType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Form\FormMapper;

class ElectedRepresentativeAdherentMandateAdmin extends AbstractAdmin
{
    public const SERVICE_ID = 'app.admin.elected_representative_adherent_mandate';

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'type';
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('zone', AdminZoneAutocompleteType::class, [
            'btn_add' => false,
        ]);
    }
}
