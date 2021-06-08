<?php

namespace App\Security\Voter;

use App\OAuth\Model\DeviceApiUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DeviceVoter extends Voter
{
    private const PERMISSION = 'IS_DEVICE_AUTHENTICATED';

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $token->getUser() instanceof DeviceApiUser;
    }
}
