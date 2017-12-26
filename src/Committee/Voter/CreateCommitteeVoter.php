<?php

namespace AppBundle\Committee\Voter;

use AppBundle\Committee\CommitteeManager;
use AppBundle\Committee\CommitteePermissions;
use AppBundle\Entity\Adherent;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CreateCommitteeVoter extends Voter
{
    private $manager;

    public function __construct(CommitteeManager $manager)
    {
        $this->manager = $manager;
    }

    protected function supports($attribute, $subject)
    {
        return CommitteePermissions::CREATE === $attribute && null === $subject;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $adherent = $token->getUser();

        if (!$adherent instanceof Adherent || $adherent->isReferent()) {
            return false;
        }

        if ($this->manager->isSupervisorOfAnyCommittee($adherent)) {
            return false;
        }

        if ($this->manager->isCommitteeHost($adherent)) {
            return false;
        }

        if ($this->manager->hasCommitteeInStatus($adherent, CommitteeManager::COMMITTEE_STATUS_NOT_ALLOWED_TO_CREATE_ANOTHER)) {
            return false;
        }

        return true;
    }
}
