<?php

namespace App\Security\Voter\Poll;

use App\Entity\Adherent;
use App\Entity\MyTeam\DelegatedAccess;
use App\Entity\Poll\LocalPoll;
use App\Security\Voter\AbstractAdherentVoter;
use Symfony\Component\HttpFoundation\RequestStack;

class EditCandidateLocalPollVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_EDIT_CANDIDATE_LOCAL_POLL';

    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        $managedZone = null;
        if ($delegatedAccess = $adherent->getReceivedDelegatedAccessByUuid($this->requestStack->getSession()->get(DelegatedAccess::ATTRIBUTE_KEY))) {
            $adherent = $delegatedAccess->getDelegator();
        }

        if ($adherent->isCandidate()) {
            $managedZone = $adherent->getCandidateManagedArea()->getZone();
        }

        if (!$managedZone) {
            return false;
        }

        return \in_array($managedZone, $subject->getZone()->getWithParents());
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::PERMISSION === $attribute && $subject instanceof LocalPoll;
    }
}
