<?php

namespace AppBundle\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AnonymousVoter extends Voter
{
    private const IS_ANONYMOUS = 'IS_ANONYMOUS';

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return self::IS_ANONYMOUS === $attribute;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $token instanceof AnonymousToken;
    }
}
