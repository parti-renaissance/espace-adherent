<?php

namespace App\Security\Voter\Committee;

use App\Committee\CommitteePermissionEnum;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\MyTeam\DelegatedAccess;
use App\Repository\Geo\ZoneRepository;
use App\Security\Voter\AbstractAdherentVoter;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PreApprovePreRefuseCommitteeVoter extends AbstractAdherentVoter
{
    /** @var ZoneRepository */
    private $zoneRepository;
    /** @var SessionInterface */
    private $session;

    public function __construct(ZoneRepository $zoneRepository, SessionInterface $session)
    {
        $this->zoneRepository = $zoneRepository;
        $this->session = $session;
    }

    protected function supports(string $attribute, $committee): bool
    {
        return \in_array($attribute, [CommitteePermissionEnum::PRE_APPROVE, CommitteePermissionEnum::PRE_REFUSE], true)
            && $committee instanceof Committee;
    }

    /**
     * @param Committee $committee
     */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $committee): bool
    {
        if (($delegatedAccess = $adherent->getReceivedDelegatedAccessByUuid($this->session->get(DelegatedAccess::ATTRIBUTE_KEY)))
            && \in_array(DelegatedAccess::ACCESS_COMMITTEE, $delegatedAccess->getAccesses(), true)) {
            $adherent = $delegatedAccess->getDelegator();
        }

        if (!$committee->isPending() && !(CommitteePermissionEnum::PRE_APPROVE === $attribute && $committee->isPreRefused())) {
            return false;
        }

        return $this->zoneRepository->isInZones($committee->getZones()->toArray(), $adherent->getManagedArea()->getZones()->toArray());
    }
}
