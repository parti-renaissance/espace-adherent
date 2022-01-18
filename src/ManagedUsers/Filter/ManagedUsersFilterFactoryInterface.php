<?php

namespace App\ManagedUsers\Filter;

use App\ManagedUsers\ManagedUsersFilter;

interface ManagedUsersFilterFactoryInterface
{
    public function support(string $scopeCode): bool;

    public function create(array $zones): ManagedUsersFilter;
}
