<?php

namespace App\Security\Voter\Admin;

use App\Entity\Administrator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

abstract class AbstractAdminVoter extends Voter
{
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $administrator = $token->getUser();
        if (!$administrator instanceof Administrator) {
            return false;
        }

        return $this->doVoteOnAttribute($attribute, $administrator, $subject);
    }

    abstract protected function doVoteOnAttribute(string|int $attribute, Administrator $administrator, $subject): bool;
}
