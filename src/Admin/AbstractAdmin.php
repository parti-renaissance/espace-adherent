<?php

declare(strict_types=1);

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin as SonataAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;

class AbstractAdmin extends SonataAdmin
{
    protected function configureFormOptions(array &$formOptions): void
    {
        $formOptions['validation_groups'] = array_merge(
            ['Default', 'Admin'],
            $this->isCreation() ? ['Admin_creation'] : ['Admin_modification']
        );
    }

    protected function isCreation(): bool
    {
        return !$this->getSubject()?->getId();
    }

    protected function configureBatchActions(array $actions): array
    {
        return [];
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::SORT_BY] = 'id';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }
}
