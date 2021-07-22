<?php

namespace App\Security\Voter\Audience;

use App\Entity\Adherent;
use App\Entity\Audience\AbstractAudience;
use App\Entity\Audience\CandidateAudience;
use App\Entity\Audience\DeputyAudience;
use App\Entity\Audience\ReferentAudience;
use App\Entity\Audience\SenatorAudience;
use App\Entity\MyTeam\DelegatedAccess;
use App\Security\Voter\AbstractAdherentVoter;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CreateAudienceVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_CREATE_AUDIENCE';

    /** @var SessionInterface */
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if ($delegatedAccess = $adherent->getReceivedDelegatedAccessByUuid($this->session->get(DelegatedAccess::ATTRIBUTE_KEY))) {
            $adherent = $delegatedAccess->getDelegator();
        }

        if ($subject instanceof ReferentAudience && $adherent->isReferent()) {
            return true;
        } elseif ($subject instanceof DeputyAudience && $adherent->isDeputy()) {
            return true;
        } elseif ($subject instanceof SenatorAudience && $adherent->isSenator()) {
            return true;
        } elseif ($subject instanceof CandidateAudience && $adherent->isHeadedRegionalCandidate()) {
            return true;
        } else {
            return false;
        }
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof AbstractAudience;
    }
}
