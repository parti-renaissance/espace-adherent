<?php

declare(strict_types=1);

namespace App\Admin\ElectedRepresentative;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;

class ZoneAdmin extends AbstractAdmin
{
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter->add('name');
    }
}
