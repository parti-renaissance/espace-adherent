<?php

namespace App\Security\Voter;

use App\Membership\MembershipRegistrationProcess;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MembershipRegistrationVoter extends Voter
{
    public const REGISTRATION_IN_PROGRESS = 'MEMBERSHIP_REGISTRATION_IN_PROGRESS';

    private $membershipRegistrationProcess;

    public function __construct(MembershipRegistrationProcess $membershipRegistrationProcess)
    {
        $this->membershipRegistrationProcess = $membershipRegistrationProcess;
    }

    protected function supports($attribute, $subject)
    {
        return self::REGISTRATION_IN_PROGRESS === $attribute;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $this->membershipRegistrationProcess->isStarted();
    }
}
