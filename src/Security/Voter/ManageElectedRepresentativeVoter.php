<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Repository\Geo\ZoneRepository;
use App\Scope\ScopeGeneratorResolver;

class ManageElectedRepresentativeVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'MANAGE_ELECTED_REPRESENTATIVE';

    public function __construct(
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
        private readonly ZoneRepository $zoneRepository,
    ) {
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        $scope = $this->scopeGeneratorResolver->generate();

        if (!$scope) {
            return false;
        }

        $adherent = $scope->getDelegator() ?? $adherent;
        $scopeZones = $scope->getZones();

        /** @var ElectedRepresentative $subject */
        if (
            ($subject->getCreatedByAdherent() && $subject->getCreatedByAdherent()->equals($adherent))
            || ($subject->getUpdatedByAdherent() && $subject->getUpdatedByAdherent()->equals($adherent))
        ) {
            return true;
        }

        $zones = array_merge($subject->getZones()->toArray(), $subject->getAdherent() ? $subject->getAdherent()->getZones()->toArray() : []);
        foreach ($zones as $zone) {
            if ($this->zoneRepository->isInZones([$zone], $scopeZones)) {
                return true;
            }
        }

        return false;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::PERMISSION === $attribute && $subject instanceof ElectedRepresentative;
    }
}
