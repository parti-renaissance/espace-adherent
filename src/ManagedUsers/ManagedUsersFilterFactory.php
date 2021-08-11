<?php

namespace App\ManagedUsers;

use App\Entity\Adherent;
use App\Geo\ManagedZoneProvider;
use App\ManagedUsers\Filter\ManagedUsersFilterFactoryInterface;

class ManagedUsersFilterFactory
{
    private $managedZoneProvider;
    /** @var ManagedUsersFilterFactoryInterface[] */
    private $factories;

    public function __construct(ManagedZoneProvider $managedZoneProvider, iterable $factories)
    {
        $this->managedZoneProvider = $managedZoneProvider;
        $this->factories = $factories;
    }

    public function create(string $spaceCode, Adherent $adherent): ?ManagedUsersFilter
    {
        foreach ($this->factories as $factory) {
            if ($factory->support($spaceCode)) {
                return $factory->create(
                    $adherent,
                    $this->managedZoneProvider->getManagedZones($adherent, $spaceCode)
                );
            }
        }

        return null;
    }
}
