<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\MyTeam\DelegatedAccess;
use App\Repository\Geo\ZoneRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ManagedUserVoter extends AbstractAdherentVoter
{
    public const IS_MANAGED_USER = 'IS_MANAGED_USER';

    private SessionInterface $session;
    private ZoneRepository $zoneRepository;

    public function __construct(SessionInterface $session, ZoneRepository $zoneRepository)
    {
        $this->session = $session;
        $this->zoneRepository = $zoneRepository;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::IS_MANAGED_USER === $attribute && $subject instanceof Adherent;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $user, $adherent): bool
    {
        $isGranted = false;

        if ($delegatedAccess = $user->getReceivedDelegatedAccessByUuid($this->session->get(DelegatedAccess::ATTRIBUTE_KEY))) {
            $user = $delegatedAccess->getDelegator();
        }

        // Check Deputy role
        if (!$isGranted && $user->isDeputy()) {
            $isGranted = $this->zoneRepository->isInZones($adherent->getZones()->toArray(), [$user->getDeputyZone()]);
        }

        return $isGranted;
    }
}
