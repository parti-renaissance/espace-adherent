<?php

namespace App\Admin\Geo;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;

class ZoneAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues)
    {
        $sortValues['_sort_by'] = 'code';
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('name')
            ->add('code')
        ;
    }
}
