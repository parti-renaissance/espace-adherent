<?php

namespace App\Security\Voter\Poll;

use App\Entity\Adherent;
use App\Entity\MyTeam\DelegatedAccess;
use App\Entity\Poll\LocalPoll;
use App\Security\Voter\AbstractAdherentVoter;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class EditCandidateLocalPollVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_EDIT_CANDIDATE_LOCAL_POLL';

    /** @var SessionInterface */
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

        return \in_array($managedZone, $subject->getZone()->getWithParents());
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof LocalPoll;
    }
}
