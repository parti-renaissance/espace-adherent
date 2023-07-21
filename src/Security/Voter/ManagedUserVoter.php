<?php

namespace App\Security\Voter;

use App\Address\AddressInterface;
use App\Entity\Adherent;
use App\Entity\MyTeam\DelegatedAccess;
use App\Repository\Geo\ZoneRepository;
use App\Repository\ReferentTagRepository;
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

        // Check Referent role
        /** @var Adherent $adherent */
        if ($user->isReferent()) {
            $isGranted = (bool) array_intersect(
                $adherent->getReferentTagCodes(),
                $user->getManagedAreaTagCodes(),
            );
        }

        // Check Deputy role
        if (!$isGranted && $user->isDeputy()) {
            $isGranted = $this->zoneRepository->isInZones($adherent->getZones()->toArray(), [$user->getDeputyZone()]);
        }

        // Check Senator role
        if (!$isGranted && $user->isSenator()) {
            $code = $user->getSenatorArea()->getDepartmentTag()->getCode();
            $isGranted = (bool) array_intersect($adherent->getReferentTagCodes(), [$code])
                || (ReferentTagRepository::FRENCH_OUTSIDE_FRANCE_TAG === $code && AddressInterface::FRANCE !== $adherent->getCountry());
        }

        return $isGranted;
    }
}
