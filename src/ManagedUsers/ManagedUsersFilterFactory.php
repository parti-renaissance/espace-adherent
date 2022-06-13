<?php

namespace App\ManagedUsers;

use App\ManagedUsers\Filter\ManagedUsersFilterFactoryInterface;

class ManagedUsersFilterFactory
{
    /** @var iterable|ManagedUsersFilterFactoryInterface[] */
    private iterable $factories;

    public function __construct(iterable $factories)
    {
        $this->factories = $factories;
    }

    public function createForZones(string $scopeCode, array $zones): ?ManagedUsersFilter
    {
        foreach ($this->factories as $factory) {
            if ($factory->support($scopeCode)) {
                return $factory->create($zones);
            }
        }

        return null;
    }
}
