<?php

namespace App\Security\Voter;

use App\AdherentSpace\AdherentSpaceEnum;
use App\Entity\Adherent;
use App\Entity\Event\CommitteeEvent;
use App\Entity\Geo\Zone;
use App\Entity\MyTeam\DelegatedAccess;
use App\Entity\ZoneableEntityInterface;
use App\Entity\ZoneableWithScopeEntityInterface;
use App\Geo\ManagedZoneProvider;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ManageZoneableItemVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'MANAGE_ZONEABLE_ITEM__';

    public function __construct(
        private readonly SessionInterface $session,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
        private readonly ManagedZoneProvider $managedZoneProvider,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if ($scope = $this->scopeGeneratorResolver->generate()) {
            $adherent = $scope->getDelegator() ?? $adherent;
            $zoneIds = array_map(fn (Zone $zone) => $zone->getId(), $scope->getZones());
            $committeeUuids = $scope->getCommitteeUuids();
        } elseif ($delegatedAccess = $adherent->getReceivedDelegatedAccessByUuid($this->session->get(DelegatedAccess::ATTRIBUTE_KEY))) {
            $adherent = $delegatedAccess->getDelegator();
        }

        if ($subject instanceof ZoneableWithScopeEntityInterface && $scopeCode = $subject->getScope()) {
            if (!$this->authorizationChecker->isGranted(RequestScopeVoter::PERMISSION, $scopeCode)) {
                return false;
            }

            $spaceType = AdherentSpaceEnum::SCOPES[$scopeCode];
        } elseif ($scope) {
            $spaceType = $scope->getMainCode();
        } else {
            $spaceType = $this->getSpaceType($attribute);
        }

        if (!empty($committeeUuids) && $subject instanceof CommitteeEvent) {
            return \in_array($subject->getCommitteeUuid(), $committeeUuids);
        }

        if (empty($zoneIds) && !$zoneIds = $this->managedZoneProvider->getManagedZonesIds($adherent, $spaceType)) {
            return false;
        }

        foreach ($subject->getZones() as $zone) {
            if ($this->managedZoneProvider->zoneBelongsToSome($zone, $zoneIds)) {
                return true;
            }
        }

        return false;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return str_starts_with($attribute, self::PERMISSION) && $subject instanceof ZoneableEntityInterface;
    }

    private function getSpaceType(string $attribute): string
    {
        return mb_strtolower(substr($attribute, \strlen(self::PERMISSION)));
    }
}
