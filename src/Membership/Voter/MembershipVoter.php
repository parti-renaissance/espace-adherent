<?php

namespace AppBundle\Membership\Voter;

use AppBundle\Membership\MembershipPermissions;
use AppBundle\Entity\Adherent;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class MembershipVoter implements VoterInterface
{
    public function vote(TokenInterface $token, $subject, array $attributes)
    {
        $adherent = $token->getUser();

        if (null !== $subject || !$adherent instanceof Adherent) {
            return self::ACCESS_ABSTAIN;
        }

        if (!in_array(MembershipPermissions::UNREGISTER, $attributes, true)) {
            return self::ACCESS_ABSTAIN;
        }

        return $adherent->isBasicAdherent() ? self::ACCESS_GRANTED : self::ACCESS_DENIED;
    }
}
