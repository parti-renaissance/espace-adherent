<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\EntityScopeVisibilityInterface;
use App\Entity\EntityScopeVisibilityWithZoneInterface;
use App\Entity\EntityScopeVisibilityWithZonesInterface;
use App\Entity\Jecoute\News;
use App\Entity\Phoning\Campaign;
use App\Entity\Team\Team;
use App\Geo\ManagedZoneProvider;
use App\Repository\Geo\ZoneRepository;
use App\Scope\ScopeEnum;
use App\Scope\ScopeGeneratorResolver;

class ScopeVisibilityVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'SCOPE_CAN_MANAGE';

    public function __construct(
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
        private readonly ManagedZoneProvider $managedZoneProvider,
        private readonly ZoneRepository $zoneRepository,
    ) {
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        $scope = $this->scopeGeneratorResolver->generate();

        if (!$scope || !$subject instanceof EntityScopeVisibilityInterface) {
            return false;
        }

        // National scope
        if ($scope->isNational()) {
            return $subject->isNationalVisibility();
        }

        // Local scope & National subject
        if ($subject->isNationalVisibility()) {
            if (\in_array($subject::class, [Team::class, Campaign::class], true)) {
                return false;
            }

            return ScopeEnum::LEGISLATIVE_CANDIDATE !== $scope->getMainCode();
        }

        if (
            ($committeeUuids = $scope->getCommitteeUuids())
            && $subject instanceof News
        ) {
            return \in_array($subject->committee->getUuid()->toString(), $committeeUuids, true);
        }

        // Local scope & Local subject
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

    protected function supports(string $attribute, $subject): bool
    {
        return self::PERMISSION === $attribute && $subject instanceof EntityScopeVisibilityInterface;
    }
}
