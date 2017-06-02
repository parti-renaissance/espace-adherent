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

        return $this->voteOnCreateCommitteeAttribute($adherent);
    }

    private function voteOnCreateCommitteeAttribute(Adherent $adherent)
    {
        if ($adherent->isReferent()) {
            return self::ACCESS_DENIED;
        }

        return $this->manager->isCommitteeHost($adherent) ? self::ACCESS_DENIED : self::ACCESS_GRANTED;
    }
}
