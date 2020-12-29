<?php

namespace App\Security\Voter\Committee;

use App\Committee\CommitteePermissions;
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

    protected function supports($attribute, $committee)
    {
        return \in_array($attribute, [CommitteePermissions::PRE_APPROVE, CommitteePermissions::PRE_REFUSE], true)
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

        if (!$adherent->isReferent()) {
            return false;
        }

        if (!$committee->isPending() && !(CommitteePermissions::PRE_APPROVE === $attribute && $committee->isPreRefused())) {
            return false;
        }

        return $this->zoneRepository->isInZones($committee->getZones()->toArray(), $adherent->getManagedArea()->getZones()->toArray());
    }
}
