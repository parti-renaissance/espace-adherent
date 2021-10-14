<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CanAccessEventVoter extends Voter
{
    public const PERMISSION = 'CAN_ACCESS_EVENT';

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $token->getUser() instanceof Adherent || !$subject->isPrivate();
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof BaseEvent;
    }
}
