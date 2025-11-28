<?php

declare(strict_types=1);

namespace App\Security\Voter\Admin;

use App\Entity\Administrator;
use App\Entity\Geo\Zone;
use App\Entity\ZoneableEntityInterface;
use App\Geo\ManagedZoneProvider;

class ZoneableEntityVoter extends AbstractAdminVoter
{
    // Hack: using int instead of string to avoid renaming by using prefix ROLE_ by Sonata RoleSecurityHandler
    public const int ROLE_ADMIN_OBJECT_IN_USER_ZONES = 1024;

    public function __construct(private readonly ManagedZoneProvider $managedZoneProvider)
    {
    }

    protected function supports(string $attribute, $subject): bool
    {
        return (string) self::ROLE_ADMIN_OBJECT_IN_USER_ZONES === $attribute;
    }

    protected function doVoteOnAttribute(string|int $attribute, Administrator $administrator, $subject): bool
    {
        if ($administrator->getZones()->isEmpty()) {
            return true;
        }

        if (!$subject instanceof ZoneableEntityInterface) {
            return false;
        }

        $managedZonesId = $administrator->getZones()->map(static fn (Zone $zone) => $zone->getId())->toArray();

        foreach ($subject->getZones() as $zone) {
            if ($this->managedZoneProvider->zoneBelongsToSome($zone, $managedZonesId)) {
                return true;
            }
        }

        return false;
    }
}
