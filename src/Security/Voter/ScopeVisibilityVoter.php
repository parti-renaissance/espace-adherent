<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\EntityScopeVisibilityInterface;
use App\Entity\EntityScopeVisibilityWithZoneInterface;
use App\Entity\EntityScopeVisibilityWithZonesInterface;
use App\Geo\ManagedZoneProvider;
use App\Repository\Geo\ZoneRepository;
use App\Scope\ScopeGeneratorResolver;

class ScopeVisibilityVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'SCOPE_CAN_MANAGE';

    private ScopeGeneratorResolver $scopeGeneratorResolver;
    private ManagedZoneProvider $managedZoneProvider;
    private ZoneRepository $zoneRepository;

    public function __construct(
        ScopeGeneratorResolver $scopeGeneratorResolver,
        ManagedZoneProvider $managedZoneProvider,
        ZoneRepository $zoneRepository
    ) {
        $this->scopeGeneratorResolver = $scopeGeneratorResolver;
        $this->managedZoneProvider = $managedZoneProvider;
        $this->zoneRepository = $zoneRepository;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        $scope = $this->scopeGeneratorResolver->generate();

        if (!$scope) {
            return false;
        }

        if ($scope->isNational()) {
            if ($subject instanceof EntityScopeVisibilityWithZoneInterface) {
                return null === $subject->getZone();
            }

            if ($subject instanceof EntityScopeVisibilityWithZonesInterface) {
                return 0 === $subject->getZones()->count();
            }

            return false;
        }

        // scope local
        if ($subject instanceof EntityScopeVisibilityWithZoneInterface) {
            if (null === $subject->getZone()) {
                return false;
            }

            return $this->managedZoneProvider->zoneBelongsToSomeZones($subject->getZone(), $scope->getZones());
        }

        if ($subject instanceof EntityScopeVisibilityWithZonesInterface) {
            if (0 === $subject->getZones()->count()) {
                return false;
            }

            return $this->zoneRepository->isInZones($subject->getZones()->toArray(), $scope->getZones());
        }

        return false;
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof EntityScopeVisibilityInterface;
    }
}
