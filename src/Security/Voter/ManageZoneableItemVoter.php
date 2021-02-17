<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\MyTeam\DelegatedAccess;
use App\Entity\ZoneableEntity;
use App\Geo\ManagedZoneProvider;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ManageZoneableItemVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'MANAGE_ZONEABLE_ITEM__';

    private $session;
    private $managedZoneProvider;

    public function __construct(SessionInterface $session, ManagedZoneProvider $managedZoneProvider)
    {
        $this->session = $session;
        $this->managedZoneProvider = $managedZoneProvider;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        /** @var ZoneableEntity $subject */
        if ($delegatedAccess = $adherent->getReceivedDelegatedAccessByUuid($this->session->get(DelegatedAccess::ATTRIBUTE_KEY))) {
            $adherent = $delegatedAccess->getDelegator();
        }

        if (!$zoneIds = $this->managedZoneProvider->getManagedZonesIds($adherent, $this->getSpaceType($attribute))) {
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
