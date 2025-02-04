<?php

namespace App\Security\Voter\Event;

use App\Entity\Adherent;
use App\Entity\Event\Event;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CanAccessEventVoter extends Voter
{
    public const PERMISSION = 'CAN_ACCESS_EVENT';

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        return $token->getUser() instanceof Adherent || !$subject->isPrivate();
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::PERMISSION === $attribute && $subject instanceof Event;
    }
}
