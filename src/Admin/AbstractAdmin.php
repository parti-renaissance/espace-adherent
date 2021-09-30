<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin as SonataAdmin;

class AbstractAdmin extends SonataAdmin
{
    protected function isCreation(): bool
    {
        return !(bool) $this->getSubject()->getId();
    }

    protected function configureBatchActions($actions)
    {
        return [];
    }

    protected function configureDefaultSortValues(array &$sortValues)
    {
        $sortValues['_sort_order'] = 'DESC';
    }
}
