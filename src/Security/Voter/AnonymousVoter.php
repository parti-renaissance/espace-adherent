<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AnonymousVoter extends Voter
{
    private const IS_ANONYMOUS = 'IS_ANONYMOUS';

    protected function supports(string $attribute, $subject): bool
    {
        return self::IS_ANONYMOUS === $attribute;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        return $token instanceof AnonymousToken || $token instanceof NullToken;
    }
}
