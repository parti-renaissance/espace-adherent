<?php

namespace App\ManagedUsers\Filter;

use App\ManagedUsers\ManagedUsersFilter;

abstract class AbstractFilterFactory implements ManagedUsersFilterFactoryInterface
{
    abstract protected function getSubscriptionType(): string;

    protected function updateFilter(ManagedUsersFilter $filter): void
    {
    }

    final public function create(array $zones): ManagedUsersFilter
    {
        $this->updateFilter($filter = new ManagedUsersFilter($this->getSubscriptionType(), $zones));

        return $filter;
    }
}
