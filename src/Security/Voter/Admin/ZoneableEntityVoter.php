<?php

declare(strict_types=1);

namespace App\Security\Voter\Admin;

use App\Entity\Administrator;
use App\Entity\ZoneableEntityInterface;
use App\Repository\Geo\ZoneRepository;

class ZoneableEntityVoter extends AbstractAdminVoter
{
    // Hack: using int instead of string to avoid renaming by using prefix ROLE_ by Sonata RoleSecurityHandler
    public const int ROLE_ADMIN_OBJECT_IN_USER_ZONES = 1024;

    public function __construct(private readonly ZoneRepository $zoneRepository)
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

        $managedZones = $administrator->getZones()->toArray();

        foreach ($subject->getZones() as $zone) {
            if ($this->zoneRepository->isInZones([$zone], $managedZones)) {
                return true;
            }
        }

        return false;
    }
}
