<?php

namespace App\Security\Voter;

use App\AdherentCharter\AdherentCharterTypeEnum;
use App\Entity\Adherent;
use App\Entity\MyTeam\DelegatedAccess;
use Symfony\Component\HttpFoundation\RequestStack;

class CharterVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_ACCEPT_CHARTER';

    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if ($delegatedAccess = $adherent->getReceivedDelegatedAccessByUuid($this->requestStack->getSession()->get(DelegatedAccess::ATTRIBUTE_KEY))) {
            $adherent = $delegatedAccess->getDelegator();
        }

        switch ($subject) {
            case AdherentCharterTypeEnum::TYPE_PHONING_CAMPAIGN:
                return $adherent->isPhoningCampaignTeamMember();
            case AdherentCharterTypeEnum::TYPE_CANDIDATE:
                return $adherent->isCandidate();
            case AdherentCharterTypeEnum::TYPE_COMMITTEE_HOST:
                return $adherent->isSupervisor() || $adherent->isHost();
            case AdherentCharterTypeEnum::TYPE_DEPUTY:
                return $adherent->isDeputy();
            case AdherentCharterTypeEnum::TYPE_LEGISLATIVE_CANDIDATE:
                return $adherent->isLegislativeCandidate();
        }

        return true;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::PERMISSION === $attribute
            && \is_string($subject)
            && AdherentCharterTypeEnum::isValid($subject);
    }
}
