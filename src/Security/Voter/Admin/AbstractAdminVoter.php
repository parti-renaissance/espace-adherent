<?php

namespace AppBundle\Security\Voter\Admin;

use AppBundle\Entity\Administrator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

abstract class AbstractAdminVoter extends Voter
{
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $administrator = $token->getUser();
        if (!$administrator instanceof Administrator) {
            return false;
        }

        return $this->doVoteOnAttribute($attribute, $administrator, $subject);
    }

    abstract protected function doVoteOnAttribute(string $attribute, Administrator $administrator, $subject): bool;
}
