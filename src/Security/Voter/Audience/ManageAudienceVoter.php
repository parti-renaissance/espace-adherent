<?php

namespace App\Security\Voter\Audience;

use App\AdherentSpace\AdherentSpaceEnum;
use App\Entity\Adherent;
use App\Entity\Audience\AbstractAudience;
use App\Entity\Audience\CandidateAudience;
use App\Entity\Audience\DeputyAudience;
use App\Entity\Audience\ReferentAudience;
use App\Entity\Audience\SenatorAudience;
use App\Entity\MyTeam\DelegatedAccess;
use App\Geo\ManagedZoneProvider;
use App\Repository\Geo\ZoneRepository;
use App\Security\Voter\AbstractAdherentVoter;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ManageAudienceVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_MANAGE_AUDIENCE';

    /** @var SessionInterface */
    private $session;
    private $zoneRepository;
    private $managedZoneProvider;

    public function __construct(
        SessionInterface $session,
        ZoneRepository $zoneRepository,
        ManagedZoneProvider $managedZoneProvider
    ) {
        $this->session = $session;
        $this->zoneRepository = $zoneRepository;
        $this->managedZoneProvider = $managedZoneProvider;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if ($delegatedAccess = $adherent->getReceivedDelegatedAccessByUuid($this->session->get(DelegatedAccess::ATTRIBUTE_KEY))) {
            $adherent = $delegatedAccess->getDelegator();
        }

        if ($subject instanceof ReferentAudience && $adherent->isReferent()) {
            $spaceType = AdherentSpaceEnum::REFERENT;
        } elseif ($subject instanceof DeputyAudience && $adherent->isDeputy()) {
            $spaceType = AdherentSpaceEnum::DEPUTY;
        } elseif ($subject instanceof SenatorAudience && $adherent->isSenator()) {
            $spaceType = AdherentSpaceEnum::SENATOR;
        } elseif ($subject instanceof CandidateAudience && $adherent->isHeadedRegionalCandidate()) {
            $spaceType = AdherentSpaceEnum::CANDIDATE;
        } else {
            return false;
        }

        return $this->zoneRepository->isInZones(
            [$subject->getZone()],
            $this->managedZoneProvider->getManagedZones($adherent, $spaceType)
        );
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof AbstractAudience;
    }
}
