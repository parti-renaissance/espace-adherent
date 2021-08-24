<?php

namespace App\Security\Voter;

use App\AdherentSpace\AdherentSpaceEnum;
use App\Entity\Adherent;
use App\Entity\MyTeam\DelegatedAccess;
use App\Entity\ZoneableEntity;
use App\Entity\ZoneableWithScopeEntity;
use App\Geo\ManagedZoneProvider;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ManageZoneableItemVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'MANAGE_ZONEABLE_ITEM__';

    private $session;
    private $managedZoneProvider;
    private $authorizationChecker;

    public function __construct(
        SessionInterface $session,
        ManagedZoneProvider $managedZoneProvider,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->session = $session;
        $this->managedZoneProvider = $managedZoneProvider;
        $this->authorizationChecker = $authorizationChecker;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        /** @var ZoneableEntity $subject */
        if ($delegatedAccess = $adherent->getReceivedDelegatedAccessByUuid($this->session->get(DelegatedAccess::ATTRIBUTE_KEY))) {
            $adherent = $delegatedAccess->getDelegator();
        }

        if ($subject instanceof ZoneableWithScopeEntity && $scope = $subject->getScope()) {
            if (!$this->authorizationChecker->isGranted(RequestScopeVoter::PERMISSION, $scope)) {
                return false;
            }

            $spaceType = AdherentSpaceEnum::SCOPES[$scope];
        } else {
            $spaceType = $this->getSpaceType($attribute);
        }

        if (!$zoneIds = $this->managedZoneProvider->getManagedZonesIds($adherent, $spaceType)) {
            return false;
        }

        foreach ($subject->getZones() as $zone) {
            if ($this->managedZoneProvider->zoneBelongsToSome($zone, $zoneIds)) {
                return true;
            }
        }

        return false;
    }

    protected function supports($attribute, $subject)
    {
        return 0 === strpos($attribute, self::PERMISSION) && $subject instanceof ZoneableEntity;
    }

    private function getSpaceType(string $attribute): string
    {
        return mb_strtolower(substr($attribute, \strlen(self::PERMISSION)));
    }
}
