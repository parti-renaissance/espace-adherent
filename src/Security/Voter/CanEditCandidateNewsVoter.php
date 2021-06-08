<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\Jecoute\News;
use App\Entity\MyTeam\DelegatedAccess;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CanEditCandidateNewsVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_EDIT_CANDIDATE_JECOUTE_NEWS';

    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        $managedZone = null;
        if ($delegatedAccess = $adherent->getReceivedDelegatedAccessByUuid($this->session->get(DelegatedAccess::ATTRIBUTE_KEY))) {
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

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof News;
    }
}
