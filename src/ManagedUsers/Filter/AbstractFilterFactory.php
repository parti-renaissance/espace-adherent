<?php

namespace App\ManagedUsers\Filter;

use App\ManagedUsers\ManagedUsersFilter;

abstract class AbstractFilterFactory implements ManagedUsersFilterFactoryInterface
{
    abstract protected function getSubscriptionType(): string;

    final public function create(array $zones): ManagedUsersFilter
    {
        return new ManagedUsersFilter($this->getSubscriptionType(), $zones);
    }
}
