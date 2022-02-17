<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\PushToken;
use App\OAuth\Model\DeviceApiUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PushTokenAuthorVoter extends Voter
{
    private const PERMISSION = 'IS_AUTHOR_OF_PUSH_TOKEN';

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof PushToken;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if ($user instanceof Adherent) {
            return $subject->getAdherent()->equals($user);
        } elseif ($user instanceof DeviceApiUser && $subject->getDevice()) {
            return $subject->getDevice()->equals($user->getDevice());
        }

        return false;
    }
}
