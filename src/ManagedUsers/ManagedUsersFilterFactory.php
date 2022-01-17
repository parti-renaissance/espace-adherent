<?php

namespace App\ManagedUsers;

use App\Entity\Adherent;
use App\ManagedUsers\Filter\ManagedUsersFilterFactoryInterface;
use App\Scope\GeneralScopeGenerator;

class ManagedUsersFilterFactory
{
    /** @var iterable|ManagedUsersFilterFactoryInterface[] */
    private iterable $factories;
    private GeneralScopeGenerator $scopeGenerator;

    public function __construct(GeneralScopeGenerator $scopeGenerator, iterable $factories)
    {
        $this->scopeGenerator = $scopeGenerator;
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

    public function createForScope(string $scopeCode, Adherent $adherent): ?ManagedUsersFilter
    {
        $scope = $this->scopeGenerator->getGenerator($scopeCode, $adherent)->generate($adherent);

        return $this->createForZones($scopeCode, $scope->getZones());
    }
}
