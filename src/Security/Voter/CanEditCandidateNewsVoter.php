<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\Jecoute\News;
use App\Entity\MyTeam\DelegatedAccess;
use Symfony\Component\HttpFoundation\RequestStack;

class CanEditCandidateNewsVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_EDIT_CANDIDATE_JECOUTE_NEWS';

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

        return \in_array($managedZone, $subject->getZone()->getWithParents(), true);
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::PERMISSION === $attribute && $subject instanceof News;
    }
}
