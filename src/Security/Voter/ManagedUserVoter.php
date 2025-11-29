<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Repository\Geo\ZoneRepository;

class ManagedUserVoter extends AbstractAdherentVoter
{
    public const IS_MANAGED_USER = 'IS_MANAGED_USER';

    public function __construct(private readonly ZoneRepository $zoneRepository)
    {
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::IS_MANAGED_USER === $attribute && $subject instanceof Adherent;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $user, $adherent): bool
    {
        $isGranted = false;

        // Check Deputy role
        if (!$isGranted && $user->isDeputy()) {
            $isGranted = $this->zoneRepository->isInZones($adherent->getZones()->toArray(), [$user->getDeputyZone()]);
        }

        return $isGranted;
    }
}
