<?php

namespace AppBundle\Committee\Voter;

use AppBundle\Committee\CommitteeManager;
use AppBundle\Committee\CommitteePermissions;
use AppBundle\Entity\Adherent;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class CreateCommitteeVoter implements VoterInterface
{
    private $manager;

    public function __construct(CommitteeManager $manager)
    {
        $this->manager = $manager;
    }

    public function vote(TokenInterface $token, $subject, array $attributes)
    {
        $adherent = $token->getUser();
        if (null !== $subject || !$adherent instanceof Adherent) {
            return self::ACCESS_ABSTAIN;
        }

        if (!in_array(CommitteePermissions::CREATE, $attributes, true)) {
            return self::ACCESS_ABSTAIN;
        }

        if ($adherent->isReferent()) {
            return self::ACCESS_DENIED;
        }

        if ($this->manager->isSupervisorOfAnyCommittee($adherent)) {
            return self::ACCESS_DENIED;
        }

        if ($this->manager->isCommitteeHost($adherent)) {
            return self::ACCESS_DENIED;
        }

        if ($this->manager->hasCommitteeInStatus($adherent, CommitteeManager::COMMITTEE_STATUS_NOT_ALLOWED_TO_CREATE_ANOTHER)) {
            return self::ACCESS_DENIED;
        }

        return self::ACCESS_GRANTED;
    }
}
